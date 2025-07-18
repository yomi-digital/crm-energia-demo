<?php

namespace Database\Seeders\AlfacomSqlite;

use App\Models\Product;
use App\Models\Brand;
use App\Models\Feeband;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $this->createEnergyProducts();
        $this->createTelephonyProducts();
    }

    private function createEnergyProducts()
    {
        $energyBrands = Brand::where('category', 'Energia')->get();
        
        $energyProducts = [
            [
                'name' => 'Luce Verde Residenziale',
                'notes' => 'Offerta energia elettrica 100% rinnovabile per abitazioni',
                'discount_percent' => 15,
                'enabled' => 1
            ],
            [
                'name' => 'Luce Verde Business',
                'notes' => 'Offerta energia elettrica 100% rinnovabile per aziende',
                'discount_percent' => 20,
                'enabled' => 1
            ],
            [
                'name' => 'Dual Fuel Residenziale',
                'notes' => 'Offerta combinata luce e gas per abitazioni',
                'discount_percent' => 18,
                'enabled' => 1
            ],
            [
                'name' => 'Dual Fuel Business',
                'notes' => 'Offerta combinata luce e gas per aziende',
                'discount_percent' => 25,
                'enabled' => 1
            ],
            [
                'name' => 'Solo Gas Residenziale',
                'notes' => 'Offerta gas naturale per abitazioni',
                'discount_percent' => 12,
                'enabled' => 1
            ],
            [
                'name' => 'Solo Gas Business',
                'notes' => 'Offerta gas naturale per aziende',
                'discount_percent' => 18,
                'enabled' => 1
            ]
        ];

        foreach ($energyProducts as $productData) {
            $product = Product::firstOrCreate([
                'name' => $productData['name']
            ], $productData);

            // Assegna un brand casuale se disponibile
            if ($energyBrands->count() > 0) {
                $product->brand_id = $energyBrands->random()->id;
                $product->save();
            }

            // Crea feeband di default
            $this->createDefaultFeeband($product, 'Energia');
        }
    }

    private function createTelephonyProducts()
    {
        $telephonyBrands = Brand::where('category', 'Telefonia')->get();
        
        $telephonyProducts = [
            [
                'name' => 'Mobile Unlimited',
                'notes' => 'Piano mobile con minuti e SMS illimitati + 50GB',
                'discount_percent' => 10,
                'enabled' => 1
            ],
            [
                'name' => 'Mobile Business',
                'notes' => 'Piano mobile business con minuti illimitati + 100GB',
                'discount_percent' => 15,
                'enabled' => 1
            ],
            [
                'name' => 'Fibra 1GB',
                'notes' => 'Connessione fibra ottica fino a 1GB/s',
                'discount_percent' => 20,
                'enabled' => 1
            ],
            [
                'name' => 'Fibra 2.5GB',
                'notes' => 'Connessione fibra ottica fino a 2.5GB/s',
                'discount_percent' => 25,
                'enabled' => 1
            ],
            [
                'name' => 'ADSL 20MB',
                'notes' => 'Connessione ADSL fino a 20MB/s',
                'discount_percent' => 15,
                'enabled' => 1
            ],
            [
                'name' => 'Mobile + Fibra',
                'notes' => 'Pacchetto mobile + fibra ottica',
                'discount_percent' => 30,
                'enabled' => 1
            ],
            [
                'name' => 'Business Complete',
                'notes' => 'Pacchetto business completo mobile + fibra',
                'discount_percent' => 35,
                'enabled' => 1
            ]
        ];

        foreach ($telephonyProducts as $productData) {
            $product = Product::firstOrCreate([
                'name' => $productData['name']
            ], $productData);

            // Assegna un brand casuale se disponibile
            if ($telephonyBrands->count() > 0) {
                $product->brand_id = $telephonyBrands->random()->id;
                $product->save();
            }

            // Crea feeband di default
            $this->createDefaultFeeband($product, 'Telefonia');
        }
    }

    private function createDefaultFeeband($product, $category)
    {
        // Crea feeband di default con compensi realistici
        $baseFee = $category === 'Energia' ? 25 : 30;
        
        Feeband::firstOrCreate([
            'product_id' => $product->id,
            'is_default' => 1
        ], [
            'product_id' => $product->id,
            'is_default' => 1,
            'start_date' => null,
            'end_date' => null,
            'fee_type' => 'FISSO',
            'management_fee' => $baseFee * 0.1, // 10% del compenso base
            'top_partner_fee' => $baseFee * 0.15, // 15% del compenso base
            'top_fee' => $baseFee * 0.25, // 25% del compenso base
            'partner_fee' => $baseFee * 0.35, // 35% del compenso base
            'smart_fee' => $baseFee * 0.5, // 50% del compenso base
            'collaborator_fee' => $baseFee * 0.4, // 40% del compenso base
        ]);

        // Crea anche alcune fasce temporali di esempio
        $this->createTemporalFeebands($product, $baseFee);
    }

    private function createTemporalFeebands($product, $baseFee)
    {
        $temporalFeebands = [
            [
                'start_date' => '2024-01-01',
                'end_date' => '2024-03-31',
                'multiplier' => 1.2 // +20% per promozione invernale
            ],
            [
                'start_date' => '2024-04-01',
                'end_date' => '2024-06-30',
                'multiplier' => 1.1 // +10% per promozione primaverile
            ],
            [
                'start_date' => '2024-07-01',
                'end_date' => '2024-09-30',
                'multiplier' => 1.3 // +30% per promozione estiva
            ]
        ];

        foreach ($temporalFeebands as $feebandData) {
            Feeband::firstOrCreate([
                'product_id' => $product->id,
                'start_date' => $feebandData['start_date'],
                'end_date' => $feebandData['end_date']
            ], [
                'product_id' => $product->id,
                'is_default' => 0,
                'start_date' => $feebandData['start_date'],
                'end_date' => $feebandData['end_date'],
                'fee_type' => 'FISSO',
                'management_fee' => $baseFee * 0.1 * $feebandData['multiplier'],
                'top_partner_fee' => $baseFee * 0.15 * $feebandData['multiplier'],
                'top_fee' => $baseFee * 0.25 * $feebandData['multiplier'],
                'partner_fee' => $baseFee * 0.35 * $feebandData['multiplier'],
                'smart_fee' => $baseFee * 0.5 * $feebandData['multiplier'],
                'collaborator_fee' => $baseFee * 0.4 * $feebandData['multiplier'],
            ]);
        }
    }
} 
