<?php

namespace Database\Seeders\Alfacom;

use App\Models\User;
use Carbon\Carbon;

trait LinkSeeder
{
    private function links($mysqli)
    {
        $legacy = $mysqli->query("SELECT * FROM datatype_verificas")->fetch_all(MYSQLI_ASSOC);
        foreach ($legacy as $entry) {
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

                $data = [
                    'name' => $entry['nome_servizio'],
                    'url' => $entry['link'],
                ];

                // Imposta created_at e updated_at se disponibili
                if ($createdAt) {
                    $data['created_at'] = $createdAt;
                    $data['updated_at'] = $updatedAt;
                }

                $newModel = \App\Models\Link::create($data);
            } catch (\Exception $e) {
                dump('Cannot create link ' . $entry['id'] . ' Error: ' . substr($e->getMessage(), 0, 120) . '...');
            }
        }
    }
}
