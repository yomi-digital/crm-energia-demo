<?php

namespace Database\Seeders\Alfacom;

use App\Models\User;

trait LinkSeeder
{
    private function links($mysqli)
    {
        $legacy = $mysqli->query("SELECT * FROM datatype_verificas")->fetch_all(MYSQLI_ASSOC);
        foreach ($legacy as $entry) {
            try {
                $newModel = \App\Models\Link::create([
                    'name' => $entry['nome_servizio'],
                    'url' => $entry['link'],
                ]);
            } catch (\Exception $e) {
                dump('Cannot create link ' . $entry['id'] . ' Error: ' . substr($e->getMessage(), 0, 120) . '...');
            }
        }
    }
}
