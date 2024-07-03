<?php

namespace Database\Seeders\Alfacom;

use App\Models\User;

trait BrandSeeder
{
    private function brands($mysqli)
    {
        $legacy = $mysqli->query("SELECT * FROM datatype_brands")->fetch_all(MYSQLI_ASSOC);
        foreach ($legacy as $entry) {
            try {
                $newModel = \App\Models\Brand::create([
                    'legacy_id' => $entry['id'],
                    'name' => $entry['brand'],
                    'notes' => $entry['note'],
                    'expiry' => $entry['scadenza'],
                    'enabled' => $entry['attivo'] === 'SI' ? 1 : 0,
                ]);
            } catch (\Exception $e) {
                dump('Cannot create brand ' . $agency['id'] . ' Error: ' . substr($e->getMessage(), 0, 120) . '...');
            }
        }
    }
}
