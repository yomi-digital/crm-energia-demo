<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AIPaperwork;
use App\Models\User;
use App\Services\AIPaperworkAssignment;

class RebalanceAIPaperworks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aipaperworks:rebalance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Riassegna le pratiche AI orfane e quelle con assegnazioni scadute al backoffice meno carico per brand.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Inizio ribilanciamento AIPaperworks...');

        // ORFANE: pratiche senza assigned_backoffice_id ma con brand_id e non confermate
        $this->info('Processo pratiche orfane...');
        AIPaperwork::query()
            ->whereNull('assigned_backoffice_id')
            ->whereNotNull('brand_id')
            ->where('status', '!=', 5)
            ->chunkById(100, function ($paperworks) {
                foreach ($paperworks as $paperwork) {
                    $this->info('Analisi della pratica orfana ' . $paperwork->id . ' per il brand ' . $paperwork->brand_id);
                    $assignment = AIPaperworkAssignment::assignToBackofficeByBrand($paperwork->brand_id);

                    if ($assignment['assigned_backoffice_id']) {
                        $paperwork->assigned_backoffice_id = $assignment['assigned_backoffice_id'];
                        $paperwork->assignment_status = $assignment['assignment_status'];
                        $paperwork->assignment_expires_at = $assignment['assignment_expires_at'];
                        $paperwork->save();
                        $this->info('Assegnazione della pratica orfana ' . $paperwork->id . ' al backoffice ' . $assignment['assigned_backoffice_id'] . ' per il brand ' . $paperwork->brand_id);
                    }else {
                        $this->info('Sembra che non sia stato possibile trovare un nuovo backoffice per la pratica orfana' . $paperwork->id . ' per il brand ' . $paperwork->brand_id . ' per questo resta orfana');
                    }
                }
            });

        // SCADUTE: pratiche con backoffice assegnato, brand noto, non confermate, timeout scaduto
        // Importante: consideriamo solo quelle NON ancora accettate dal backoffice
        // (assignment_status null o 'pending'). Le pratiche già accettate non vengono
        // riassegnate dal cron.
        $this->info('Processo pratiche con assegnazioni scadute...');
        AIPaperwork::query()
            ->whereNotNull('assigned_backoffice_id')
            ->whereNotNull('assignment_expires_at')
            ->where('assignment_expires_at', '<', now())
            ->whereNotNull('brand_id')
            ->where('status', '!=', 5)
            ->where(function ($query) {
                $query->whereNull('assignment_status')
                      ->orWhere('assignment_status', 'pending');
            })
            ->chunkById(100, function ($paperworks) {
                foreach ($paperworks as $paperwork) {
                    $currentBackofficeId = $paperwork->assigned_backoffice_id;
                    $this->info('Analisi della pratica ' . $paperwork->id . ' assegnata attualmente al backoffice ' . $currentBackofficeId . ' per il brand ' . $paperwork->brand_id . ' (con tempo scaduto) ' . $paperwork->assignment_expires_at);

                    // Tenta di trovare un nuovo backoffice per questo brand escludendo il proprietario attuale
                    $this->info('Tentativo di trovare un nuovo backoffice per la pratica ' . $paperwork->id . ' per il brand ' . $paperwork->brand_id);
                    $assignment = AIPaperworkAssignment::assignToBackofficeByBrand(
                        $paperwork->brand_id,
                        $currentBackofficeId
                    );
                    $this->info('Risultato del tentativo di trovare un nuovo backoffice: ' . $assignment['assigned_backoffice_id'] ?? 'null' . ' per la pratica ' . $paperwork->id . ' per il brand ' . $paperwork->brand_id);

                    // Se non esiste un altro backoffice per questo brand
                    if (
                        !$assignment['assigned_backoffice_id'] ||
                        $assignment['assigned_backoffice_id'] === $currentBackofficeId
                    ) {
                        $this->info('Sembra che non sia stato possibile trovare un nuovo backoffice per la pratica ' . $paperwork->id . ' per il brand ' . $paperwork->brand_id);
                        
                        // Verifica se il backoffice attuale ha ancora il brand abilitato
                        $currentBackoffice = User::find($currentBackofficeId);
                        
                        if ($currentBackoffice && $currentBackoffice->brands()->where('brands.id', $paperwork->brand_id)->exists()) {
                            // Il backoffice ha ancora il brand, mantiene la pratica  a lui
                            $this->info('Il backoffice precedente -> ' . $currentBackofficeId . ' -> ha ancora il brand ' . $paperwork->brand_id . ' quindi mantiene la pratica a lui');
                            continue;
                        } else {
                            // Il backoffice ha perso il brand, la pratica diventa orfana
                            $this->info('Il backoffice precedente -> ' . $currentBackofficeId . ' -> ha perso il brand ' . $paperwork->brand_id . ' quindi la pratica diventa orfana perché non ci sono altri backoffice con quel brand abilitato');
                            $paperwork->assigned_backoffice_id = null;
                            $paperwork->assignment_status = null;
                            $paperwork->assignment_expires_at = null;
                            $paperwork->save();
                            continue;
                        }
                    }

                    $previousBackofficeId = $currentBackofficeId;

                    $this->info('Assegnazione della pratica ' . $paperwork->id . ' al backoffice ' . $assignment['assigned_backoffice_id'] . ' per il brand ' . $paperwork->brand_id);
                    $paperwork->assigned_backoffice_id = $assignment['assigned_backoffice_id'];
                    $paperwork->assignment_status = $assignment['assignment_status'];
                    $paperwork->assignment_expires_at = $assignment['assignment_expires_at'];

                    $paperwork->save();
                }
            });

        $this->info('Ribilanciamento AIPaperworks completato.');

        return Command::SUCCESS;
    }
}
