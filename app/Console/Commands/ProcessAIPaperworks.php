<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AIPaperwork;
use App\Services\ContractProcessingService;

class ProcessAIPaperworks extends Command
{
    protected $signature = 'aipaperworks:process';
    protected $description = 'Process one pending AIPaperwork with status 0';

    protected $contractProcessingService;

    public function __construct(ContractProcessingService $contractProcessingService)
    {
        parent::__construct();
        $this->contractProcessingService = $contractProcessingService;
    }

    public function handle()
    {
        // Get only the oldest pending paperwork
        $paperwork = AIPaperwork::where('status', 0)
            ->orderBy('created_at', 'asc')
            ->first();

        if (!$paperwork) {
            $this->info('No pending AIPaperworks to process.');
            return;
        }

        try {
            $this->info(sprintf('Processing AIPaperwork ID: %d', $paperwork->id));
            $this->contractProcessingService->processContract($paperwork);
            $this->info(sprintf('Successfully processed AIPaperwork ID: %d', $paperwork->id));
        } catch (\Exception $e) {
            // Update status to error (9)
            $paperwork->status = 9;
            $paperwork->save();
            
            $this->error(sprintf('Failed to process AIPaperwork ID: %d. Error: %s', $paperwork->id, $e->getMessage()));
        }
    }
} 
