<?php

namespace Database\Seeders\AlfacomSqlite;

use App\Models\Paperwork;
use App\Models\User;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Mandate;
use Illuminate\Database\Seeder;

class PaperworkSeeder extends Seeder
{
    public function run()
    {
        $this->createEnergyPaperworks();
        $this->createTelephonyPaperworks();
    }

    private function createEnergyPaperworks()
    {
        $users = User::all();
        $customers = Customer::all();
        $products = Product::all();
        $mandates = Mandate::all();

        $energyProducts = $products->whereIn('name', [
            'Luce Verde Residenziale',
            'Luce Verde Business',
            'Dual Fuel Residenziale',
            'Dual Fuel Business',
            'Solo Gas Residenziale',
            'Solo Gas Business'
        ]);

        $energyPaperworks = [
            [
                'account_pod_pdr' => 'IT001E12345678',
                'partner_sent_at' => '2024-01-15',
                'order_status' => 'Confermato',
                'partner_outcome_at' => '2024-01-20',
                'owner_notes' => 'Pratica energia elettrica residenziale',
                'contract_type' => 'Residenziale',
                'notes' => 'Cliente interessato a offerta luce verde',
                'partner_outcome' => 'Accettato',
                'order_code' => 'EN001',
                'paid' => 1,
                'coverage' => 'Lombardia',
                'annual_consumption' => 2500,
                'category' => 'Energia',
                'confirmed_at' => '2024-01-16',
                'type' => 'Nuovo Contratto',
                'energy_type' => 'Elettrica'
            ],
            [
                'account_pod_pdr' => 'IT001G87654321',
                'partner_sent_at' => '2024-01-20',
                'order_status' => 'In Elaborazione',
                'partner_outcome_at' => null,
                'owner_notes' => 'Pratica gas naturale business',
                'contract_type' => 'Business',
                'notes' => 'Cliente aziendale, necessita documentazione completa',
                'partner_outcome' => 'In Attesa',
                'order_code' => 'EN002',
                'paid' => 0,
                'coverage' => 'Lazio',
                'annual_consumption' => 15000,
                'category' => 'Energia',
                'confirmed_at' => '2024-01-21',
                'type' => 'Nuovo Contratto',
                'energy_type' => 'Gas'
            ],
            [
                'account_pod_pdr' => 'IT001E98765432',
                'partner_sent_at' => '2024-02-01',
                'order_status' => 'Confermato',
                'partner_outcome_at' => '2024-02-05',
                'owner_notes' => 'Pratica dual fuel residenziale',
                'contract_type' => 'Residenziale',
                'notes' => 'Cliente vuole combinare luce e gas',
                'partner_outcome' => 'Accettato',
                'order_code' => 'EN003',
                'paid' => 1,
                'coverage' => 'Piemonte',
                'annual_consumption' => 3000,
                'category' => 'Energia',
                'confirmed_at' => '2024-02-02',
                'type' => 'Nuovo Contratto',
                'energy_type' => 'Dual Fuel'
            ]
        ];

        foreach ($energyPaperworks as $paperworkData) {
            $this->createPaperwork($paperworkData, $users, $customers, $energyProducts, $mandates);
        }
    }

    private function createTelephonyPaperworks()
    {
        $users = User::all();
        $customers = Customer::all();
        $products = Product::all();
        $mandates = Mandate::all();

        $telephonyProducts = $products->whereIn('name', [
            'Mobile Unlimited',
            'Mobile Business',
            'Fibra 1GB',
            'Fibra 2.5GB',
            'ADSL 20MB',
            'Mobile + Fibra',
            'Business Complete'
        ]);

        $telephonyPaperworks = [
            [
                'account_pod_pdr' => 'TIM123456789',
                'partner_sent_at' => '2024-01-25',
                'order_status' => 'Confermato',
                'partner_outcome_at' => '2024-01-30',
                'owner_notes' => 'Pratica mobile residenziale',
                'contract_type' => 'Residenziale',
                'notes' => 'Cliente interessato a piano mobile unlimited',
                'partner_outcome' => 'Accettato',
                'order_code' => 'TL001',
                'paid' => 1,
                'coverage' => 'Toscana',
                'annual_consumption' => null,
                'category' => 'Telefonia',
                'confirmed_at' => '2024-01-26',
                'type' => 'Nuovo Contratto',
                'energy_type' => null
            ],
            [
                'account_pod_pdr' => 'VOD987654321',
                'partner_sent_at' => '2024-02-05',
                'order_status' => 'In Elaborazione',
                'partner_outcome_at' => null,
                'owner_notes' => 'Pratica fibra business',
                'contract_type' => 'Business',
                'notes' => 'Cliente aziendale, necessita connessione veloce',
                'partner_outcome' => 'In Attesa',
                'order_code' => 'TL002',
                'paid' => 0,
                'coverage' => 'Emilia-Romagna',
                'annual_consumption' => null,
                'category' => 'Telefonia',
                'confirmed_at' => '2024-02-06',
                'type' => 'Nuovo Contratto',
                'energy_type' => null
            ],
            [
                'account_pod_pdr' => 'WIND456789123',
                'partner_sent_at' => '2024-02-10',
                'order_status' => 'Confermato',
                'partner_outcome_at' => '2024-02-15',
                'owner_notes' => 'Pratica mobile + fibra',
                'contract_type' => 'Residenziale',
                'notes' => 'Cliente vuole pacchetto completo',
                'partner_outcome' => 'Accettato',
                'order_code' => 'TL003',
                'paid' => 1,
                'coverage' => 'Lombardia',
                'annual_consumption' => null,
                'category' => 'Telefonia',
                'confirmed_at' => '2024-02-11',
                'type' => 'Nuovo Contratto',
                'energy_type' => null
            ]
        ];

        foreach ($telephonyPaperworks as $paperworkData) {
            $this->createPaperwork($paperworkData, $users, $customers, $telephonyProducts, $mandates);
        }
    }

    private function createPaperwork($paperworkData, $users, $customers, $products, $mandates)
    {
        $agent = $users->where('agent_code', 'AG001')->first();
        $customer = $customers->random();
        $product = $products->random();
        $mandate = $mandates->random();
        $confirmedBy = $users->where('agent_code', 'BO001')->first();

        Paperwork::firstOrCreate([
            'order_code' => $paperworkData['order_code']
        ], array_merge($paperworkData, [
            'user_id' => $agent ? $agent->id : null,
            'customer_id' => $customer ? $customer->id : null,
            'product_id' => $product ? $product->id : null,
            'mandate_id' => $mandate ? $mandate->id : null,
            'confirmed_by' => $confirmedBy ? $confirmedBy->id : null
        ]));
    }
} 
