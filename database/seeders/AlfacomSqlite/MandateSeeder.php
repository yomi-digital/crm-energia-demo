<?php

namespace Database\Seeders\AlfacomSqlite;

use App\Models\Mandate;
use Illuminate\Database\Seeder;

class MandateSeeder extends Seeder
{
    public function run()
    {
        $this->createMandates();
    }

    private function createMandates()
    {
        $mandates = [
            [
                'name' => 'Mandato Generale Energia',
                'notes' => 'Mandato per la gestione di contratti energia elettrica e gas'
            ],
            [
                'name' => 'Mandato Telefonia Mobile',
                'notes' => 'Mandato per la gestione di contratti telefonia mobile'
            ],
            [
                'name' => 'Mandato Fibra Ottica',
                'notes' => 'Mandato per la gestione di contratti fibra ottica e ADSL'
            ],
            [
                'name' => 'Mandato Business Completo',
                'notes' => 'Mandato per la gestione di soluzioni business complete'
            ],
            [
                'name' => 'Mandato Dual Fuel',
                'notes' => 'Mandato per la gestione di contratti luce e gas combinati'
            ],
            [
                'name' => 'Mandato Energia Verde',
                'notes' => 'Mandato per la gestione di contratti energia rinnovabile'
            ],
            [
                'name' => 'Mandato Telefonia Fissa',
                'notes' => 'Mandato per la gestione di contratti telefonia fissa'
            ],
            [
                'name' => 'Mandato Servizi Cloud',
                'notes' => 'Mandato per la gestione di servizi cloud e digitali'
            ]
        ];

        foreach ($mandates as $mandateData) {
            Mandate::firstOrCreate([
                'name' => $mandateData['name']
            ], $mandateData);
        }
    }
} 
