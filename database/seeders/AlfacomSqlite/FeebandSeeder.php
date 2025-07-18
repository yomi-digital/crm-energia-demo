<?php

namespace Database\Seeders\AlfacomSqlite;

use App\Models\BrandUser;
use App\Models\User;
use App\Models\Brand;
use Illuminate\Database\Seeder;

class FeebandSeeder extends Seeder
{
    public function run()
    {
        $this->createBrandUserAssociations();
    }

    private function createBrandUserAssociations()
    {
        $users = User::all();
        $brands = Brand::all();

        // Associazioni predefinite per utenti di test
        $associations = [
            // Mario Rossi (AG001) - Energia
            [
                'user_email' => 'mario.rossi@alfacom.com',
                'brand_name' => 'ENEL Energia',
                'pay_level' => 'SMART'
            ],
            [
                'user_email' => 'mario.rossi@alfacom.com',
                'brand_name' => 'A2A Energia',
                'pay_level' => 'SMART'
            ],
            // Giulia Bianchi (AG002) - Telefonia
            [
                'user_email' => 'giulia.bianchi@alfacom.com',
                'brand_name' => 'TIM',
                'pay_level' => 'SMART'
            ],
            [
                'user_email' => 'giulia.bianchi@alfacom.com',
                'brand_name' => 'Vodafone',
                'pay_level' => 'SMART'
            ],
            // Luca Verdi (ST001) - Struttura Energia
            [
                'user_email' => 'luca.verdi@alfacom.com',
                'brand_name' => 'ENEL Energia',
                'pay_level' => 'TOP'
            ],
            [
                'user_email' => 'luca.verdi@alfacom.com',
                'brand_name' => 'A2A Energia',
                'pay_level' => 'TOP'
            ],
            [
                'user_email' => 'luca.verdi@alfacom.com',
                'brand_name' => 'ENI Plenitude',
                'pay_level' => 'TOP'
            ],
            // Anna Neri (TL001) - Team Leader Telefonia
            [
                'user_email' => 'anna.neri@alfacom.com',
                'brand_name' => 'TIM',
                'pay_level' => 'TOP_PARTNER'
            ],
            [
                'user_email' => 'anna.neri@alfacom.com',
                'brand_name' => 'Vodafone',
                'pay_level' => 'TOP_PARTNER'
            ],
            [
                'user_email' => 'anna.neri@alfacom.com',
                'brand_name' => 'Wind Tre',
                'pay_level' => 'TOP_PARTNER'
            ],
            // Paolo Gialli (BO001) - Backoffice
            [
                'user_email' => 'paolo.gialli@alfacom.com',
                'brand_name' => 'ENEL Energia',
                'pay_level' => 'PARTNER'
            ],
            [
                'user_email' => 'paolo.gialli@alfacom.com',
                'brand_name' => 'TIM',
                'pay_level' => 'PARTNER'
            ]
        ];

        foreach ($associations as $association) {
            $user = $users->where('email', $association['user_email'])->first();
            $brand = $brands->where('name', $association['brand_name'])->first();

            if ($user && $brand) {
                BrandUser::firstOrCreate([
                    'user_id' => $user->id,
                    'brand_id' => $brand->id
                ], [
                    'user_id' => $user->id,
                    'brand_id' => $brand->id,
                    'pay_level' => $association['pay_level']
                ]);
            }
        }

        // Crea anche alcune associazioni casuali per simulare un ambiente realistico
        $this->createRandomAssociations($users, $brands);
    }

    private function createRandomAssociations($users, $brands)
    {
        $agents = $users->whereIn('agent_code', ['AG001', 'AG002']);
        $energyBrands = $brands->where('category', 'Energia');
        $telephonyBrands = $brands->where('category', 'Telefonia');

        // Associa agenti a brand casuali
        foreach ($agents as $agent) {
            $profile = $agent->commercial_profile;
            
            if ($profile === 'Energia') {
                $selectedBrands = $energyBrands->random(rand(2, 4));
            } elseif ($profile === 'Telefonia') {
                $selectedBrands = $telephonyBrands->random(rand(2, 4));
            } else {
                $selectedBrands = $brands->random(rand(1, 3));
            }

            foreach ($selectedBrands as $brand) {
                BrandUser::firstOrCreate([
                    'user_id' => $agent->id,
                    'brand_id' => $brand->id
                ], [
                    'user_id' => $agent->id,
                    'brand_id' => $brand->id,
                    'pay_level' => $this->getRandomPayLevel()
                ]);
            }
        }
    }

    private function getRandomPayLevel()
    {
        $levels = ['SMART', 'COLLABORATORE', 'PARTNER', 'TOP', 'TOP_PARTNER'];
        return $levels[array_rand($levels)];
    }
} 
