<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixImportedCustomer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:imported_customer';

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
        $this->info('Funzione da implementare');

        return self::SUCCESS;
    }
}


