<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AIPaperwork;
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
                    $assignment = AIPaperworkAssignment::assignToBackofficeByBrand($paperwork->brand_id);

                    if ($assignment['assigned_backoffice_id']) {
                        $paperwork->assigned_backoffice_id = $assignment['assigned_backoffice_id'];
                        $paperwork->assignment_status = $assignment['assignment_status'];
                        $paperwork->assignment_expires_at = $assignment['assignment_expires_at'];
                        $paperwork->save();
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

                    // Tenta di trovare un nuovo backoffice per questo brand escludendo il proprietario attuale
                    $assignment = AIPaperworkAssignment::assignToBackofficeByBrand(
                        $paperwork->brand_id,
                        $currentBackofficeId
                    );

                    // Se non esiste un altro backoffice per questo brand, non cambiamo proprietario
                    if (
                        !$assignment['assigned_backoffice_id'] ||
                        $assignment['assigned_backoffice_id'] === $currentBackofficeId
                    ) {
                        continue;
                    }

                    $previousBackofficeId = $currentBackofficeId;

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
