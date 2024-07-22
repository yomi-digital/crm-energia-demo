<?php

namespace Database\Seeders\Alfacom;

use App\Models\Paperwork;

trait PaperworkSeeder
{
    private function paperworks($mysqli)
    {
        mysqli_query($mysqli, 'ALTER TABLE datatype_pratiches ADD new_id BIGINT');
        $legacy = $mysqli->query("SELECT * FROM datatype_pratiches")->fetch_all(MYSQLI_ASSOC);
        foreach ($legacy as $entry) {
            try {
                $data = [
                    'legacy_id' => $entry['id'],
                    'user_id' => null,
                    'account_pod_pdr' => $entry['account_pod_pdr'],
                    'partner_sent_at' => $entry['data_inserimento'] === '0000-00-00' ? null : $entry['data_inserimento'],
                    'order_status' => $entry['stato_ordine'],
                    'partner_outcome_at' => $entry['data_esito_partner'],
                    'owner_notes' => $entry['note_alfacom'],
                    'customer_id' => null,
                    'manager_id' => null,
                    'contract_type' => $entry['tipo_contratto'],
                    'product_id' => null,
                    'notes' => $entry['note_pratica'],
                    'partner_outcome' => $entry['esito_partner'],
                    'order_code' => $entry['codice_ordine'],
                    'paid' => $entry['pagato'] && strtolower($entry['pagato']) === 'si' ? 1 : 0,
                    // 'appointment' => $entry['appuntamento'] && strtolower($entry['appuntamento']) === 'si' ? 1 : 0,
                    'appointment_id' => null,
                    'mandate_id' => null,
                    'coverage' => $entry['copertura'],
                    'canceled_at' => $entry['data_storno'],
                    'expired_at' => $entry['data_scadenza'],
                    'annual_consumption' => $entry['consumo_annuo'],
                    'category' => $entry['tipologia'],
                    'confirmed_at' => $entry['confirmed_at'],
                    'confirmed_by' => null,
                    'type' => $entry['tipo'],
                    'energy_type' => $entry['tipo_energia'],
                    // 'pdf_url' => $entry['url_pdf'],
                ];

                if ($entry['id_agente']) {
                    $user = \App\Models\User::where('legacy_id', $entry['id_agente'])->first();
                    if ($user) {
                        $data['user_id'] = $user->id;
                    }
                }

                if ($entry['district_manager']) {
                    $user = \App\Models\User::where('legacy_id', $entry['district_manager'])->first();
                    if ($user) {
                        $data['manager_id'] = $user->id;
                    }
                }

                if ($entry['id_cliente']) {
                    $customer = \App\Models\Customer::where('legacy_id', $entry['id_cliente'])->first();
                    if ($customer) {
                        $data['customer_id'] = $customer->id;
                    }
                }

                if ($entry['agenzia']) {
                    $agency = \App\Models\Mandate::where('name', $entry['agenzia'])->first();
                    if ($agency) {
                        $data['mandate_id'] = $agency->id;
                    }
                }

                if ($entry['id_servizio']) {
                    $product = \App\Models\Product::where('legacy_id', $entry['id_servizio'])->first();
                    if ($product) {
                        $data['product_id'] = $product->id;
                    }
                }


                if ($entry['confirmed_by']) {
                    $user = \App\Models\User::where('legacy_id', $entry['confirmed_by'])->first();
                    if ($user) {
                        $data['confirmed_by'] = $user->id;
                    }
                }
                // if ($entry['deleted_by']) {
                //     $user = \App\Model\User::where('legacy_id', $entry['deleted_by'])->first();
                //     if ($user) {
                //         $data['deleted_by'] = $user->id;
                //     }
                // }

                $newModel = \App\Models\Paperwork::create($data);
                if ($entry['url_pdf']) {
                    $newModel->documents()->create([
                        'url' => $entry['url_pdf'],
                        'name' => basename($entry['url_pdf']),
                    ]);
                }
            } catch (\Exception $e) {
                dump('Cannot create paperwork legacy_id ' . $entry['id'] . ' Error: ' . substr($e->getMessage(), 0, 120) . '...');
            }
        }
    }
}
