<?php

namespace Database\Seeders\Alfacom;

use App\Models\Customer;

trait CustomerSeeder
{
    private function customers($mysqli)
    {
        mysqli_query($mysqli, 'ALTER TABLE datatype_clientis ADD new_id BIGINT');
        $legacyCustomers = $mysqli->query("SELECT * FROM datatype_clientis")->fetch_all(MYSQLI_ASSOC);
        foreach ($legacyCustomers as $customer) {
            try {
                $data = [
                    'legacy_id' => $customer['id'],
                    'name' => $customer['nome'],
                    'last_name' => $customer['cognome'],
                    'email' => $customer['email'],
                    'phone' => $customer['telefono'],
                    'mobile' => $customer['cellulare'],
                    'business_name' => $customer['ragione_sociale'],
                    'tax_id_code' => $customer['codice_fiscale'],
                    'vat_number' => $customer['partita_iva'],
                    'ateco_code' => $customer['codice_ateco'],
                    'pec' => $customer['pec'],
                    'unique_code' => $customer['codice_univoco'],
                    'category' => $customer['tipologia'],
                    'address' => $customer['indirizzo'],
                    'region' => strtoupper($customer['regione']),
                    'province' => $customer['provincia'],
                    'city' => strtoupper($customer['citta']),
                    'zip' => $customer['cap'],
                    'added_at' => $customer['data_inserimento'] === '0000-00-00' ? null : $customer['data_inserimento'],
                    'added_by' => null,
                    'confirmed' => $customer['confirmed'],
                    'confirmed_at' => $customer['confirmed_at'],
                    'confirmed_by' => null,
                    'deleted_at' => $customer['deleted_at'],
                    'deleted_by' => null,
                ];

                if ($customer['inserito_da']) {
                    $user = \App\Models\User::where('legacy_id', $customer['inserito_da'])->first();
                    if ($user) {
                        $data['added_by'] = $user->id;
                    }
                }
                if ($customer['confirmed_by']) {
                    $user = \App\Models\User::where('legacy_id', $customer['confirmed_by'])->first();
                    if ($user) {
                        $data['confirmed_by'] = $user->id;
                    }
                }
                if ($customer['deleted_by']) {
                    $user = \App\Models\User::where('legacy_id', $customer['deleted_by'])->first();
                    if ($user) {
                        $data['deleted_by'] = $user->id;
                    }
                }

                $newCustomer = Customer::create($data);
                mysqli_query($mysqli, "UPDATE datatype_clientis SET new_id = {$newCustomer->id} WHERE id = {$customer['id']}");
            } catch (\Exception $e) {
                dump('Cannot create customer legacy_id ' . $customer['id'] . ' Error: ' . substr($e->getMessage(), 0, 120) . '...');
            }
        }
    }
}
