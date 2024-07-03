<?php

namespace Database\Seeders\Alfacom;

use App\Models\User;

trait MandateSeeder
{
    private function mandates($mysqli)
    {
        $agencies = $mysqli->query("SELECT * FROM datatype_agenzies")->fetch_all(MYSQLI_ASSOC);
        foreach ($agencies as $agency) {
            try {
                $newMandate = \App\Models\Mandate::create([
                    'name' => $agency['nome'],
                    'notes' => $agency['note'],
                ]);
            } catch (\Exception $e) {
                dump('Cannot create mandate ' . $agency['id'] . ' Error: ' . substr($e->getMessage(), 0, 120) . '...');
            }
        }
    }
}
