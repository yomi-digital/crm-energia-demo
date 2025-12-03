<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GeocodingService;

class GeocodingController extends Controller
{
    protected $geocodingService;

    public function __construct(GeocodingService $geocodingService)
    {
        $this->geocodingService = $geocodingService;
    }

    /**
     * Ottiene il CAP da città e indirizzo
     * 
     * GET /api/geocoding/postal-code?city=Milano&street=Via Roma 1
     */
    public function getPostalCode(Request $request)
    {
        $request->validate([
            'city' => 'required|string|max:255',
            'street' => 'required|string|max:255',
        ], [
            'city.required' => 'La città è obbligatoria',
            'street.required' => 'L\'indirizzo è obbligatorio',
        ]);

        $city = $request->get('city');
        $street = $request->get('street');

        $result = $this->geocodingService->getPostalCode($city, $street);

        if (!$result['success']) {
            return response()->json([
                'error' => $result['error']
            ], 404);
        }

        return response()->json([
            'postal_code' => $result['postal_code'],
            'formatted_address' => $result['formatted_address'],
            'latitude' => $result['latitude'],
            'longitude' => $result['longitude'],
        ]);
    }
}
