<?php

namespace App\Jobs;

use App\Models\AIPaperwork;
use App\Services\ContractProcessingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAIPaperworkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $aiPaperworkId;

    /**
     * Create a new job instance.
     */
    public function __construct($aiPaperworkId)
    {
        $this->aiPaperworkId = $aiPaperworkId;
    }

    /**
     * Execute the job.
     */
    public function handle(ContractProcessingService $contractProcessingService)
    {
        try {
            $aiPaperwork = AIPaperwork::findOrFail($this->aiPaperworkId);
            
            // Elabora il contratto tramite il service
            $contractProcessingService->processContract($aiPaperwork);
            
            Log::info("AI Paperwork {$this->aiPaperworkId} processato con successo");
            
        } catch (\Exception $e) {
            Log::error("Errore durante l'elaborazione di AI Paperwork {$this->aiPaperworkId}: " . $e->getMessage());
            
            // Imposta status a errore se il paperwork esiste ancora
            try {
                $aiPaperwork = AIPaperwork::find($this->aiPaperworkId);
                if ($aiPaperwork) {
                    $aiPaperwork->status = 9; // Error
                    $aiPaperwork->save();
                }
            } catch (\Exception $updateException) {
                Log::error("Impossibile aggiornare lo status dell'AI Paperwork {$this->aiPaperworkId}: " . $updateException->getMessage());
            }
            
            // Rilancia l'eccezione per permettere a Laravel di gestire i retry
            throw $e;
        }
    }

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 600; // 10 minuti per elaborazioni complesse
}

