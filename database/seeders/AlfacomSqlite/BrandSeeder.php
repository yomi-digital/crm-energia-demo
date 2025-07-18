<?php

namespace Database\Seeders\AlfacomSqlite;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run()
    {
        $this->createEnergyBrands();
        $this->createTelephonyBrands();
    }

    private function createEnergyBrands()
    {
        $energyBrands = [
            [
                'name' => 'ENEL Energia',
                'notes' => 'Fornitore energia elettrica nazionale',
                'expiry' => '2024-12-31',
                'enabled' => 1,
                'category' => 'Energia',
                'type' => 'Residenziale'
            ],
            [
                'name' => 'ENEL Energia Business',
                'notes' => 'Fornitore energia elettrica per aziende',
                'expiry' => '2024-12-31',
                'enabled' => 1,
                'category' => 'Energia',
                'type' => 'Business'
            ],
            [
                'name' => 'A2A Energia',
                'notes' => 'Fornitore energia Lombardia',
                'expiry' => '2024-12-31',
                'enabled' => 1,
                'category' => 'Energia',
                'type' => 'Residenziale'
            ],
            [
                'name' => 'A2A Energia Corporate',
                'notes' => 'Fornitore energia aziendale Lombardia',
                'expiry' => '2024-12-31',
                'enabled' => 1,
                'category' => 'Energia',
                'type' => 'Business'
            ],
            [
                'name' => 'ENI Plenitude',
                'notes' => 'Fornitore energia ENI',
                'expiry' => '2024-12-31',
                'enabled' => 1,
                'category' => 'Energia',
                'type' => 'Residenziale'
            ],
            [
                'name' => 'Enegan',
                'notes' => 'Fornitore energia verde',
                'expiry' => '2024-12-31',
                'enabled' => 1,
                'category' => 'Energia',
                'type' => 'Residenziale'
            ],
            [
                'name' => 'Green Network',
                'notes' => 'Fornitore energia rinnovabile',
                'expiry' => '2024-12-31',
                'enabled' => 1,
                'category' => 'Energia',
                'type' => 'Residenziale'
            ],
            [
                'name' => 'Sorgenia',
                'notes' => 'Fornitore energia e gas',
                'expiry' => '2024-12-31',
                'enabled' => 1,
                'category' => 'Energia',
                'type' => 'Residenziale'
            ],
            [
                'name' => 'Edison Energia',
                'notes' => 'Fornitore energia storica',
                'expiry' => '2024-12-31',
                'enabled' => 1,
                'category' => 'Energia',
                'type' => 'Residenziale'
            ],
            [
                'name' => 'Engie Energia',
                'notes' => 'Fornitore energia francese',
                'expiry' => '2024-12-31',
                'enabled' => 1,
                'category' => 'Energia',
                'type' => 'Residenziale'
            ],
            [
                'name' => 'Hera Comm',
                'notes' => 'Fornitore energia Emilia Romagna',
                'expiry' => '2024-12-31',
                'enabled' => 1,
                'category' => 'Energia',
                'type' => 'Residenziale'
            ]
        ];

        foreach ($energyBrands as $brandData) {
            Brand::firstOrCreate([
                'name' => $brandData['name']
            ], $brandData);
        }
    }

    private function createTelephonyBrands()
    {
        $telephonyBrands = [
            [
                'name' => 'TIM',
                'notes' => 'Telecom Italia Mobile',
                'expiry' => '2024-12-31',
                'enabled' => 1,
                'category' => 'Telefonia',
                'type' => 'Residenziale'
            ],
            [
                'name' => 'TIM Business',
                'notes' => 'Telecom Italia Mobile Business',
                'expiry' => '2024-12-31',
                'enabled' => 1,
                'category' => 'Telefonia',
                'type' => 'Business'
            ],
            [
                'name' => 'Vodafone',
                'notes' => 'Vodafone Italia',
                'expiry' => '2024-12-31',
                'enabled' => 1,
                'category' => 'Telefonia',
                'type' => 'Residenziale'
            ],
            [
                'name' => 'Vodafone Business',
                'notes' => 'Vodafone Business Solutions',
                'expiry' => '2024-12-31',
                'enabled' => 1,
                'category' => 'Telefonia',
                'type' => 'Business'
            ],
            [
                'name' => 'Wind Tre',
                'notes' => 'Wind Tre Telecomunicazioni',
                'expiry' => '2024-12-31',
                'enabled' => 1,
                'category' => 'Telefonia',
                'type' => 'Residenziale'
            ],
            [
                'name' => 'Wind Tre Corporate',
                'notes' => 'Wind Tre Corporate Solutions',
                'expiry' => '2024-12-31',
                'enabled' => 1,
                'category' => 'Telefonia',
                'type' => 'Business'
            ],
            [
                'name' => 'Iliad',
                'notes' => 'Iliad Italia',
                'expiry' => '2024-12-31',
                'enabled' => 1,
                'category' => 'Telefonia',
                'type' => 'Residenziale'
            ],
            [
                'name' => 'Fastweb',
                'notes' => 'Fastweb Telecomunicazioni',
                'expiry' => '2024-12-31',
                'enabled' => 1,
                'category' => 'Telefonia',
                'type' => 'Residenziale'
            ],
            [
                'name' => 'Fastweb Business',
                'notes' => 'Fastweb Business Solutions',
                'expiry' => '2024-12-31',
                'enabled' => 1,
                'category' => 'Telefonia',
                'type' => 'Business'
            ],
            [
                'name' => 'Tiscali',
                'notes' => 'Tiscali Telecomunicazioni',
                'expiry' => '2024-12-31',
                'enabled' => 1,
                'category' => 'Telefonia',
                'type' => 'Residenziale'
            ]
        ];

        foreach ($telephonyBrands as $brandData) {
            Brand::firstOrCreate([
                'name' => $brandData['name']
            ], $brandData);
        }
    }
} 
