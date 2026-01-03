<?php

namespace Database\Seeders\Alfacom;

use App\Models\User;
use Carbon\Carbon;

trait ProductSeeder
{
    private function products($mysqli)
    {
        $legacy = $mysqli->query("SELECT * FROM datatype_prodottis")->fetch_all(MYSQLI_ASSOC);
        $totProducts = count($legacy);
        dump('Total products: ' . $totProducts);
        $countI = 0;
        foreach ($legacy as $entry) {
            $countI++;
            dump('Processing product ' . $countI . ' of ' . $totProducts);
            try {
                // Prepara la data di creazione originale dal database legacy
                $createdAt = null;
                $updatedAt = null;
                
                $dateField = $entry['data_inserimento'] ?? $entry['created_at'] ?? null;
                if (isset($dateField) && $dateField !== '0000-00-00' && $dateField !== null && $dateField !== '') {
                    try {
                        $createdAt = Carbon::parse($dateField);
                        $updatedAt = $createdAt;
                    } catch (\Exception $e) {
                        $createdAt = null;
                        $updatedAt = null;
                    }
                }

                $newModel = [
                    'legacy_id' => $entry['id'],
                    'name' => $entry['nome_prodotto'],
                    'brand_id' => null,
                    'notes' => $entry['note'],
                    'discount_percent' => $entry['percentuale_sconto'],
                    'enabled' => $entry['attivo'],
                    'deleted_by' => null,
                ];

                // Imposta created_at e updated_at se disponibili
                if ($createdAt) {
                    $newModel['created_at'] = $createdAt;
                    $newModel['updated_at'] = $updatedAt;
                }

                if ($entry['id_brand']) {
                    $brand = \App\Models\Brand::where('legacy_id', $entry['id_brand'])->first();
                    if ($brand) {
                        $newModel['brand_id'] = $brand->id;
                    }
                }

                if ($entry['deleted_by']) {
                    $user = \App\Models\User::where('legacy_id', $entry['deleted_by'])->first();
                    if ($user) {
                        $newModel['deleted_by'] = $user->id;
                    }
                }

                $createdProduct = \App\Models\Product::create($newModel);

                // First, create the default feeband
                $newDefaultFeeband = [
                    'product_id' => $createdProduct->id,
                    'is_default' => 1,
                    'start_date' => null,
                    'end_date' => null,
                    'fee_type' => 'FISSO',
                    'management_fee' => $entry['compenso_gestione'],
                    'top_partner_fee' => $entry['compenso_strutturetop'],
                    'top_fee' => $entry['compenso_struttura'],
                    'partner_fee' => $entry['compenso_retevendita'],
                    'smart_fee' => $entry['compenso_agente'],
                    'collaborator_fee' => $entry['compenso_procacciatore'],
                ];
                \App\Models\Feeband::create($newDefaultFeeband);

                // Now try tro create feebands for the product
                $legacyFeebands = $mysqli->query("SELECT * FROM fasce_compensi where product_id = {$entry['id']}")->fetch_all(MYSQLI_ASSOC);
                dump('Processing feebands for product ID ' . $createdProduct->id . ' (found ' . count($legacyFeebands) . ' feebands)');
                $countFeebands = 0;
                foreach ($legacyFeebands as $legacyFeeband) {
                    $countFeebands++;
                    dump('Processing feeband ' . $countFeebands . ' of ' . count($legacyFeebands));
                    $newFeeband = [
                        'product_id' => $createdProduct->id,
                        'start_date' => $legacyFeeband['data_inizio'],
                        'end_date' => $legacyFeeband['data_fine'],
                        'fee_type' => 'FISSO',
                        'management_fee' => $legacyFeeband['compenso_gestione'],
                        'top_partner_fee' => $legacyFeeband['compenso_strutturetop'],
                        'top_fee' => $legacyFeeband['compenso_struttura'],
                        'partner_fee' => $legacyFeeband['compenso_retevendita'],
                        'smart_fee' => $legacyFeeband['compenso_agente'],
                        'collaborator_fee' => $legacyFeeband['compenso_procacciatore'],
                    ];
                    \App\Models\Feeband::create($newFeeband);
                }

            } catch (\Exception $e) {
                dump('Cannot create product ' . $entry['id'] . ' Error: ' . substr($e->getMessage(), 0, 120) . '...');
            }
        }
    }
}
