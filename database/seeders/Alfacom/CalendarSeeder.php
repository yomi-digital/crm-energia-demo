<?php

namespace Database\Seeders\Alfacom;

use App\Models\User;
use Carbon\Carbon;

trait CalendarSeeder
{
    private function calendar($mysqli)
    {
        $legacy = $mysqli->query("SELECT * FROM datatype_calendarios where data_inizio is not null and data_fine is not null")->fetch_all(MYSQLI_ASSOC);
        $totCalendar = count($legacy);
        dump('Total calendar: ' . $totCalendar);
        $countI = 0;
        foreach ($legacy as $entry) {
            $countI++;
            dump('Processing calendar ' . $countI . ' of ' . $totCalendar);
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

                $agent = \App\Models\User::where('legacy_id', $entry['id_account'])->first();
                $user = \App\Models\User::where('legacy_id', $entry['inserito_da'])->first();
                if ($entry['nome_evento']) {
                    $eventTitle = $entry['nome_evento'];
                } else {
                    $eventTitle = $entry['ragione_sociale'];
                    if ($agent) {
                        $eventTitle .= ' con ' . $agent->fullName();
                    }
                }
                if ($agent) {

                }
                $data = [
                    'agent_id' => $agent ? $agent->id : null,
                    'title' => $eventTitle,
                    'notes_call_center' => $entry['note_call_center'],
                    'notes_agent' => $entry['note_agente'],
                    'notes' => $entry['note_alfacom'],
                    'status' => $entry['stato'],
                    'referent' => substr($entry['referente'], 0, 255),
                    // 'user_connection' => $entry[''],
                    'created_by' => $user ? $user->id : null,
                    // 'customer_id' => $entry[''],
                    'user_name' => $entry['ragione_sociale'],
                    'user_phone' => $entry['numero_telefono'],
                    'user_mobile' => $entry['cellulare'],
                    'user_address' => $entry['indirizzo'],
                    'user_city' => $entry['ubicazione'],
                    'type' => $entry['tipo_appuntamento'],
                    'cost' => $entry['Costo'],
                    'start' => $entry['data_inizio'],
                    'end' => $entry['data_fine'],
                    'all_day' => false,
                ];

                // Imposta created_at e updated_at se disponibili
                if ($createdAt) {
                    $data['created_at'] = $createdAt;
                    $data['updated_at'] = $updatedAt;
                }

                $newModel = \App\Models\Calendar::create($data);
            } catch (\Exception $e) {
                dump('Cannot create calendar ' . $entry['id'] . ' Error: ' . substr($e->getMessage(), 0, 120) . '...');
            }
        }
    }
}
