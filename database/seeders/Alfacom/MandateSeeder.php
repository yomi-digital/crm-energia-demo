<?php

namespace Database\Seeders\Alfacom;

use App\Models\User;
use Carbon\Carbon;

trait MandateSeeder
{
    private function mandates($mysqli)
    {
        $agencies = $mysqli->query("SELECT * FROM datatype_agenzies")->fetch_all(MYSQLI_ASSOC);
        foreach ($agencies as $agency) {
            try {
                // Prepara la data di creazione originale dal database legacy
                $createdAt = null;
                $updatedAt = null;
                
                $dateField = $agency['data_inserimento'] ?? $agency['created_at'] ?? null;
                if (isset($dateField) && $dateField !== '0000-00-00' && $dateField !== null && $dateField !== '') {
                    try {
                        $createdAt = Carbon::parse($dateField);
                        $updatedAt = $createdAt;
                    } catch (\Exception $e) {
                        $createdAt = null;
                        $updatedAt = null;
                    }
                }

                $data = [
                    'name' => $agency['nome'],
                    'notes' => $agency['note'],
                ];

                // Imposta created_at e updated_at se disponibili
                if ($createdAt) {
                    $data['created_at'] = $createdAt;
                    $data['updated_at'] = $updatedAt;
                }

                $newMandate = \App\Models\Mandate::create($data);
            } catch (\Exception $e) {
                dump('Cannot create mandate ' . $agency['id'] . ' Error: ' . substr($e->getMessage(), 0, 120) . '...');
            }
        }
    }
}
