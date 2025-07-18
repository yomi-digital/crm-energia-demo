<?php

namespace Database\Seeders\AlfacomSqlite;

use App\Models\Link;
use Illuminate\Database\Seeder;

class LinkSeeder extends Seeder
{
    public function run()
    {
        $this->createLinks();
    }

    private function createLinks()
    {
        $links = [
            [
                'name' => 'ENEL Portale Cliente',
                'url' => 'https://www.enel.it/it-IT/privati/portale-cliente'
            ],
            [
                'name' => 'A2A Area Cliente',
                'url' => 'https://www.a2a.eu/it/privati/area-cliente'
            ],
            [
                'name' => 'TIM Portale Cliente',
                'url' => 'https://www.tim.it/assistenza/area-cliente'
            ],
            [
                'name' => 'Vodafone My Vodafone',
                'url' => 'https://my.vodafone.it/'
            ],
            [
                'name' => 'Wind Tre Area Cliente',
                'url' => 'https://www.windtre.it/assistenza/area-cliente'
            ],
            [
                'name' => 'Fastweb Area Cliente',
                'url' => 'https://www.fastweb.it/area-cliente/'
            ],
            [
                'name' => 'ENI Plenitude Portale',
                'url' => 'https://www.plenitude.it/it/privati/area-cliente'
            ],
            [
                'name' => 'Enegan Area Cliente',
                'url' => 'https://www.enegan.it/area-cliente'
            ],
            [
                'name' => 'Green Network Portale',
                'url' => 'https://www.greennetwork.it/area-cliente'
            ],
            [
                'name' => 'Sorgenia Area Cliente',
                'url' => 'https://www.sorgenia.it/area-cliente'
            ],
            [
                'name' => 'Edison Area Cliente',
                'url' => 'https://www.edison.it/privati/area-cliente'
            ],
            [
                'name' => 'Engie Area Cliente',
                'url' => 'https://www.engie.it/privati/area-cliente'
            ],
            [
                'name' => 'Hera Comm Area Cliente',
                'url' => 'https://www.heracomm.it/area-cliente'
            ],
            [
                'name' => 'Iliad Area Cliente',
                'url' => 'https://www.iliad.it/area-cliente'
            ],
            [
                'name' => 'Tiscali Area Cliente',
                'url' => 'https://www.tiscali.it/area-cliente'
            ],
            [
                'name' => 'ARERA Portale',
                'url' => 'https://www.arera.it/'
            ],
            [
                'name' => 'GSE Portale',
                'url' => 'https://www.gse.it/'
            ],
            [
                'name' => 'AGCOM Portale',
                'url' => 'https://www.agcom.it/'
            ]
        ];

        foreach ($links as $linkData) {
            Link::firstOrCreate([
                'name' => $linkData['name']
            ], $linkData);
        }
    }
} 
