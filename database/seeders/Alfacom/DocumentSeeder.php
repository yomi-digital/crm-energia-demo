<?php

namespace Database\Seeders\Alfacom;

use App\Models\User;

trait DocumentSeeder
{
    private function documents($mysqli)
    {
        $legacy = $mysqli->query("SELECT * FROM datatype_documentis")->fetch_all(MYSQLI_ASSOC);
        foreach ($legacy as $entry) {
            $brandId = null;
            if ($entry['id_brand']) {
                $brand = \App\Models\Brand::where('legacy_id', $entry['id_brand'])->first();
                if ($brand) {
                    $brandId = $brand->id;
                }
            }
            try {
                $newModel = \App\Models\Document::create([
                    'name' => $entry['nome'] ?: 'Documento senza nome',
                    'added_at' => $entry['data'],
                    'url' => $entry['link'],
                    'thumb' => $entry['thumb'],
                    'notes' => $entry['note'],
                    'category' => $entry['categoria'],
                    'brand_id' => $brandId,
                ]);
            } catch (\Exception $e) {
                dump('Cannot create document ' . $entry['id'] . ' Error: ' . substr($e->getMessage(), 0, 120) . '...');
            }
        }
    }
}
