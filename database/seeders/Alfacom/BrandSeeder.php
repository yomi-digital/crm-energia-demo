<?php

namespace Database\Seeders\Alfacom;

use App\Models\User;
use Carbon\Carbon;

trait BrandSeeder
{
    private function brands($mysqli)
    {
        $legacy = $mysqli->query("SELECT * FROM datatype_brands")->fetch_all(MYSQLI_ASSOC);
        foreach ($legacy as $entry) {
            // Check the $entry['brand'], if it contains "BUSINESS, BUS, CORPORATE, AZIENDA", then set the category to "Business", else "Residenziale"
            $type = 'Residenziale';
            if (strpos(strtoupper($entry['brand']), 'BUSINESS') !== false || strpos(strtoupper($entry['brand']), 'BUS') !== false || strpos(strtoupper($entry['brand']), 'CORPORATE') !== false || strpos(strtoupper($entry['brand']), 'AZIENDA') !== false) {
                $type = 'Business';
            }
            // Che the $entry['brand'], if it contains "A2A, ENERGIA, ENI, ENEGAN, GREEN, SORGENIA, EDISON, ENEL, ENGIE, HERA, DUFERCU", then set the type as "Energia", else "Telefonia"
            $category = 'Telefonia';
            if (strpos(strtoupper($entry['brand']), 'A2A') !== false || strpos(strtoupper($entry['brand']), 'ENERGIA') !== false || strpos(strtoupper($entry['brand']), 'ENI') !== false || strpos(strtoupper($entry['brand']), 'ENEGAN') !== false || strpos(strtoupper($entry['brand']), 'GREEN') !== false || strpos(strtoupper($entry['brand']), 'SORGENIA') !== false || strpos(strtoupper($entry['brand']), 'EDISON') !== false || strpos(strtoupper($entry['brand']), 'ENEL') !== false || strpos(strtoupper($entry['brand']), 'ENGIE') !== false || strpos(strtoupper($entry['brand']), 'HERA') !== false || strpos(strtoupper($entry['brand']), 'DUFERCU') !== false) {
                $category = 'Energia';
            }

            // Normalizzazione formato: iniziale maiuscola, resto minuscolo
            $type = ucfirst(strtolower($type));
            $category = ucfirst(strtolower($category));

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

            try {
                $data = [
                    'legacy_id' => $entry['id'],
                    'name' => $entry['brand'],
                    'notes' => $entry['note'],
                    'expiry' => $entry['scadenza'],
                    'enabled' => $entry['attivo'] === 'SI' ? 1 : 0,
                    'category' => $category,
                    'type' => $type,
                ];

                // Imposta created_at e updated_at se disponibili
                if ($createdAt) {
                    $data['created_at'] = $createdAt;
                    $data['updated_at'] = $updatedAt;
                }

                $newModel = \App\Models\Brand::create($data);
            } catch (\Exception $e) {
                dump('Cannot create brand ' . $entry['id'] . ' Error: ' . substr($e->getMessage(), 0, 120) . '...');
            }
        }
    }
}
