<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    private $apiKey;
    private $baseUrl = 'https://maps.googleapis.com/maps/api/geocode/json';

    public function __construct()
    {
        $this->apiKey = config('services.google_maps.api_key');
    }

    /**
     * Ottiene il CAP da città e indirizzo
     *
     * @param string $city Città
     * @param string $street Indirizzo/Via (opzionale)
     * @return array
     */
    public function getPostalCode($city, $street = null)
    {
        if (!$this->apiKey) {
            return [
                'success' => false,
                'error' => 'Google Maps API key non configurata o non valida'
            ];
        }

        // Costruisci l'indirizzo completo
        $addressParts = array_filter([$street, $city, 'Italia']);
        $address = implode(', ', $addressParts);

        try {
            $response = Http::get($this->baseUrl, [
                'address' => $address,
                'key' => $this->apiKey,
                'language' => 'it',
                'region' => 'it'
            ]);

            if (!$response->successful()) {
                Log::error('Google Maps API error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return [
                    'success' => false,
                    'error' => 'Errore nella chiamata a Google Maps API'
                ];
            }

            $data = $response->json();

            if ($data['status'] !== 'OK' || empty($data['results'])) {
                return [
                    'success' => false,
                    'error' => 'Indirizzo non trovato',
                    'status' => $data['status']
                ];
            }

            // Estrai il CAP dai componenti dell'indirizzo
            $result = $data['results'][0];
            $postalCode = null;

            foreach ($result['address_components'] as $component) {
                if (in_array('postal_code', $component['types'])) {
                    $postalCode = $component['long_name'];
                    break;
                }
            }

            if (!$postalCode) {
                return [
                    'success' => false,
                    'error' => 'CAP non trovato per questo indirizzo'
                ];
            }

            // Estrai anche altri dati utili
            $formattedAddress = $result['formatted_address'];
            $location = $result['geometry']['location'];

            return [
                'success' => true,
                'postal_code' => $postalCode,
                'formatted_address' => $formattedAddress,
                'latitude' => $location['lat'],
                'longitude' => $location['lng'],
            ];

        } catch (\Exception $e) {
            Log::error('Geocoding service error', [
                'message' => $e->getMessage(),
                'address' => $address
            ]);

            return [
                'success' => false,
                'error' => 'Errore durante la ricerca: ' . $e->getMessage()
            ];
        }
    }
}

