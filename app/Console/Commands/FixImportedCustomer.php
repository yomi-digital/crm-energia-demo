<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\Paperwork;
use Illuminate\Support\Facades\DB;

class FixImportedCustomer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:imported_customer {--dry-run : Mostra cosa verrebbe fatto senza modificare il database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Esegue le operazioni di fix di secondo livello sui customers importati';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');

        $this->info('Inizio fix clienti importati (deduplicazione per CF/P.IVA)...');
        if ($isDryRun) {
            $this->warn('Modalità DRY-RUN attiva: nessuna modifica verrà salvata nel database.');
        }

        // Mappa customer_id => numero di pratiche, per scegliere il master
        $this->info('Calcolo numero pratiche per cliente...');
        $paperworksCountByCustomer = Paperwork::select('customer_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('customer_id')
            ->groupBy('customer_id')
            ->pluck('total', 'customer_id');

        $processedIds = [];

        Customer::orderBy('id')
            ->chunkById(500, function ($customers) use (&$processedIds, $paperworksCountByCustomer, $isDryRun) {
                foreach ($customers as $customer) {
                    // Salta se già gestito in un gruppo precedente
                    if (in_array($customer->id, $processedIds, true)) {
                        continue;
                    }

                    // Se non ha né CF né P.IVA, non è considerato per deduplica
                    if (empty($customer->tax_id_code) && empty($customer->vat_number)) {
                        continue;
                    }

                    $duplicates = $this->searchDuplicatedByTaxAndVat($customer);

                    if ($duplicates->isEmpty()) {
                        continue;
                    }

                    // Crea il gruppo: master candidate + duplicati trovati
                    $group = collect([$customer])->merge($duplicates)
                        ->filter(function (Customer $c) use (&$processedIds) {
                            return ! in_array($c->id, $processedIds, true);
                        });

                    if ($group->count() <= 1) {
                        continue;
                    }

                    $this->info("Trovati {$group->count()} clienti potenzialmente duplicati per CF/P.IVA (esaminando ID {$customer->id})");

                    // Scegli il master: quello con più pratiche (fallback a id minore in caso di pari)
                    $master = $group->sort(function (Customer $a, Customer $b) use ($paperworksCountByCustomer) {
                        $countA = $paperworksCountByCustomer[$a->id] ?? 0;
                        $countB = $paperworksCountByCustomer[$b->id] ?? 0;

                        if ($countA === $countB) {
                            return $a->id <=> $b->id;
                        }

                        return $countB <=> $countA;
                    })->first();

                    $masterCount = $paperworksCountByCustomer[$master->id] ?? 0;
                    $this->info(" - Master scelto: ID {$master->id} ({$master->name} {$master->last_name}) con {$masterCount} pratiche");

                    $toMerge = $group->where('id', '!=', $master->id);

                    foreach ($toMerge as $duplicate) {
                        $dupCount = $paperworksCountByCustomer[$duplicate->id] ?? 0;

                        if ($isDryRun) {
                            $this->line("   [DRY-RUN] Sposterei {$dupCount} pratiche da ID {$duplicate->id} ({$duplicate->name} {$duplicate->last_name}) a ID {$master->id} e eliminerei il duplicato.");
                        } else {
                            $this->info("   * Sposto {$dupCount} pratiche da ID {$duplicate->id} ({$duplicate->name} {$duplicate->last_name}) a ID {$master->id}");

                            DB::transaction(function () use ($duplicate, $master) {
                                // Sposta tutte le pratiche dal duplicato al master
                                Paperwork::where('customer_id', $duplicate->id)
                                    ->update(['customer_id' => $master->id]);

                                // Elimina il cliente duplicato
                                $duplicate->delete();
                            });

                            // Aggiorna contatori in memoria e segna come processato
                            $paperworksCountByCustomer[$master->id] =
                                ($paperworksCountByCustomer[$master->id] ?? 0) + ($dupCount ?? 0);
                            $paperworksCountByCustomer[$duplicate->id] = 0;
                        }

                        $processedIds[] = $duplicate->id;
                    }

                    // Segna anche il master come processato
                    $processedIds[] = $master->id;
                }
            });

        $this->info('Fix clienti importati completato.');

        return self::SUCCESS;
    }

    /**
     * Trova i clienti che condividono lo stesso CF o P.IVA del cliente dato.
     */
    private function searchDuplicatedByTaxAndVat(Customer $customer)
    {
        $taxId = $customer->tax_id_code ? trim($customer->tax_id_code) : null;
        $vat = $customer->vat_number ? trim($customer->vat_number) : null;

        if (empty($taxId) && empty($vat)) {
            return collect();
        }

        return Customer::query()
            ->where('id', '!=', $customer->id)
            ->where(function ($query) use ($taxId, $vat) {
                if (! empty($taxId)) {
                    $query->orWhere('tax_id_code', $taxId);
                }
                if (! empty($vat)) {
                    $query->orWhere('vat_number', $vat);
                }
            })
            ->get();
    }
}


