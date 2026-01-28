<?php

namespace App\Services;

use App\Models\AIPaperwork;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AIPaperworkAssignment
{
    /**
     * Calcola il backoffice meno carico per un determinato brand
     * e restituisce i valori da salvare su ai_paperworks.
     *
     * Ritorna un array con le chiavi:
     * - assigned_backoffice_id
     * - assignment_status
     * - assignment_expires_at
     *
     * Se non è possibile assegnare nessun backoffice (es. nessun backoffice
     * abilitato per il brand), ritorna tutti i campi a null.
     */
    public static function assignToBackofficeByBrand(?int $brandId, ?int $backofficeIdToExclude = null): array
    {
        // Se non c'è un brand associato, non possiamo usare la logica per brand
        if (!$brandId) {
            return [
                'assigned_backoffice_id' => null,
                'assignment_status' => null,
                'assignment_expires_at' => null,
            ];
        }

        // Seleziona tutti i backoffice abilitati che hanno quel brand associato
        $backofficesQuery = User::query()
            ->where('enabled', true)
            ->role('backoffice')
            ->whereHas('brands', function ($q) use ($brandId) {
                $q->where('brands.id', $brandId);
            });

        // Se è stato richiesto di escludere un backoffice specifico (es. per riassegnazioni scadute),
        // rimuovilo dall'elenco dei candidati. Se dopo l'esclusione non rimane nessun candidato,
        // ritorniamo tutti i campi a null e lasciamo il proprietario corrente invariato.
        if ($backofficeIdToExclude) {
            $backofficesQuery->where('id', '!=', $backofficeIdToExclude);
        }

        $backoffices = $backofficesQuery->get(['id']);

        if ($backoffices->isEmpty()) {
            return [
                'assigned_backoffice_id' => null,
                'assignment_status' => null,
                'assignment_expires_at' => null,
            ];
        }

        $backofficeIds = $backoffices->pluck('id')->all();

        // Conta quante pratiche AI attualmente "aperte" per brand per ciascun backoffice.
        // Consideriamo come carico tutte le pratiche assegnate (status 0,1,2, ecc.),
        // perché dal momento in cui una pratica ha un assigned_backoffice_id è in qualche
        // modo "in coda" per quel backoffice, indipendentemente dallo stato di avanzamento
        // dell'AI (pending, in elaborazione o già processata).
        //
        // Escludiamo invece:
        // - status 5 (Confermato): il lavoro del backoffice è terminato, quindi non deve
        //   più pesare sul carico corrente;
        // - status 8 (Annullato) e 9 (Errore): la pratica è uscita dal flusso normale
        //   (annullata o fallita) e non rappresenta più lavoro attivo per il backoffice,
        //   quindi non deve bloccarlo dal ricevere nuove pratiche. Se le includessimo nel
        //   conteggio del carico, un backoffice con molte pratiche annullate/errore verrebbe visto
        //   dall'algoritmo come perennemente "pieno" e rischierebbe di non ricevere mai
        //   nuove pratiche AI, anche se in realtà non ha più nulla di processabile.
        $loads = AIPaperwork::query()
            ->select('assigned_backoffice_id', DB::raw('COUNT(*) as total'))
            ->whereNotIn('status', [5, 8, 9]) // Esclude confermate, annullate, errore
            ->where('brand_id', $brandId)
            ->whereIn('assigned_backoffice_id', $backofficeIds)
            ->groupBy('assigned_backoffice_id')
            ->pluck('total', 'assigned_backoffice_id');

        // Trova il backoffice con meno pratiche aperte (carico minore)
        $selectedBackofficeId = null;
        $minLoad = null;

        foreach ($backofficeIds as $id) {
            $load = (int) ($loads[$id] ?? 0);

            if ($minLoad === null || $load < $minLoad || ($load === $minLoad && $id < $selectedBackofficeId)) {
                $minLoad = $load;
                $selectedBackofficeId = $id;
            }
        }

        if (!$selectedBackofficeId) {
            // Fallback improbabile, ma gestito per sicurezza
            return [
                'assigned_backoffice_id' => null,
                'assignment_status' => null,
                'assignment_expires_at' => null,
            ];
        }

        return [
            'assigned_backoffice_id' => $selectedBackofficeId,
            'assignment_status' => 'pending',
            'assignment_expires_at' => Carbon::now()->addMinutes(15),
        ];
    }
}

