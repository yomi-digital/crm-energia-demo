<?php

namespace Database\Seeders\Alfacom;

use App\Models\User;

trait ProductSeeder
{
    private function products($mysqli)
    {
        $legacy = $mysqli->query("SELECT * FROM datatype_prodottis")->fetch_all(MYSQLI_ASSOC);
        foreach ($legacy as $entry) {
            try {
                $newModel = [
                    'legacy_id' => $entry['id'],
                    'name' => $entry['nome_prodotto'],
                    'brand_id' => null,
                    'notes' => $entry['note'],
                    'discount_percent' => $entry['percentuale_sconto'],
                    'enabled' => $entry['attivo'],
                    'deleted_by' => null,
                ];

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

                \App\Models\Product::create($newModel);
            } catch (\Exception $e) {
                dump('Cannot create product ' . $entry['id'] . ' Error: ' . substr($e->getMessage(), 0, 120) . '...');
            }
        }
    }
}
