<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AIPaperwork;
use App\Services\ContractProcessingService;

class ProcessAIPaperworks extends Command
{
    protected $signature = 'aipaperworks:process';
    protected $description = 'Process pending AIPaperworks (status 0)';

    protected $contractProcessingService;

    public function __construct(ContractProcessingService $contractProcessingService)
    {
        parent::__construct();
        $this->contractProcessingService = $contractProcessingService;
    }

    public function handle()
    {
        // Processa documenti in attesa (status 0)
        $pending = AIPaperwork::where('status', 0)
            ->orderBy('created_at', 'asc')
            ->first();
        
        if (!$pending) {
            $this->info('No pending AIPaperworks to process.');
            return;
        }

        try {
            $this->info(sprintf('Processing AIPaperwork ID: %d', $pending->id));
            $this->contractProcessingService->processContract($pending);
            $this->info(sprintf('Successfully processed AIPaperwork ID: %d', $pending->id));
        } catch (\Exception $e) {
            // Update status to error (9)
            $pending->status = 9;
            $pending->save();
            
            $this->error(sprintf('Failed to process AIPaperwork ID: %d. Error: %s', $pending->id, $e->getMessage()));
        }
    }
} 
