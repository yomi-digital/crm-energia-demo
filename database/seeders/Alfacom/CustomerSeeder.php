<?php

namespace Database\Seeders\Alfacom;

use App\Models\Customer;
use Carbon\Carbon;

trait CustomerSeeder
{
    private function customers($mysqli)
    {
        mysqli_query($mysqli, 'ALTER TABLE datatype_clientis ADD new_id BIGINT');
        
        // Ottieni il totale prima di processare (per il progresso)
        $countResult = $mysqli->query("SELECT COUNT(*) as total FROM datatype_clientis");
        $totCustomers = $countResult->fetch_assoc()['total'];
        dump('Total customers: ' . $totCustomers);
        
        // Processa i customers in batch per evitare memory exhaustion
        $batchSize = 100;
        $offset = 0;
        $countI = 0;
        
        do {
            $result = $mysqli->query("SELECT * FROM datatype_clientis LIMIT {$batchSize} OFFSET {$offset}");
            
            if (!$result || $result->num_rows === 0) {
                break;
            }
            
            while ($customer = $result->fetch_assoc()) {
                $countI++;
                if ($countI % 10 === 0) {
                    dump('Processing customer ' . $countI . ' of ' . $totCustomers);
                }
                try {
                    // Prepara la data di creazione originale dal database legacy
                    $createdAt = null;
                    $updatedAt = null;
                    
                    if (isset($customer['data_inserimento']) && $customer['data_inserimento'] !== '0000-00-00' && $customer['data_inserimento'] !== null && $customer['data_inserimento'] !== '') {
                        try {
                            // Converte la data dal formato legacy a Carbon timestamp
                            $createdAt = Carbon::parse($customer['data_inserimento']);
                            $updatedAt = $createdAt; // Inizialmente updated_at = created_at
                        } catch (\Exception $e) {
                            // Se non riesce a parsare, usa null e Laravel imposterà la data corrente
                            $createdAt = null;
                            $updatedAt = null;
                        }
                    }

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
                        'category' => null, // verrà calcolata in base a CF / P.IVA
                        'address' => $customer['indirizzo'],
                        'region' => strtoupper($customer['regione']),
                        'province' => $customer['provincia'],
                        'city' => strtoupper($customer['citta']),
                        'zip' => $customer['cap'],
                        'added_at' => $customer['data_inserimento'] === '0000-00-00' ? null : $customer['data_inserimento'],
                        'added_by' => null,
                        'confirmed_at' => $customer['confirmed_at'],
                        'confirmed_by' => null,
                        'deleted_at' => $customer['deleted_at'],
                        'deleted_by' => null,
                    ];

                    // Imposta created_at e updated_at se disponibili, altrimenti Laravel userà la data corrente
                    if ($createdAt) {
                        $data['created_at'] = $createdAt;
                        $data['updated_at'] = $updatedAt;
                    }

                    // Calcolo category in base a codice fiscale / partita IVA
                    $hasTaxId = !empty(trim((string) $customer['codice_fiscale']));
                    $hasVat = !empty(trim((string) $customer['partita_iva']));

                    if (($hasTaxId && !$hasVat) || (!$hasTaxId && !$hasVat)) {
                        // Nessuno dei due o solo CF -> RESIDENZIALE
                        $data['category'] = 'RESIDENZIALE';
                    } elseif (!$hasTaxId && $hasVat) {
                        // Solo P.IVA -> BUSINESS
                        $data['category'] = 'BUSINESS';
                    } else {
                        // Entrambi presenti -> DITTA INDIVIDUALE: category null
                        $data['category'] = null;
                    }

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

                    // Deduplicazione: cerca se il cliente esiste già nel nuovo CRM
                    // Usa solo tax_id_code o vat_number come criteri di matching
                    $existingCustomer = null;
                    $legacyId = $customer['id'];
                    $deduplicationMethod = null;

                    // 1. Cerca per codice fiscale (se presente)
                    if (!empty($data['tax_id_code'])) {
                        $existingCustomer = Customer::where('tax_id_code', $data['tax_id_code'])->first();
                        if ($existingCustomer) {
                            $deduplicationMethod = 'tax_id_code';
                        }
                    }

                    // 2. Se non trovato, cerca per partita IVA (per clienti Business)
                    if (!$existingCustomer && !empty($data['vat_number'])) {
                        $existingCustomer = Customer::where('vat_number', $data['vat_number'])->first();
                        if ($existingCustomer) {
                            $deduplicationMethod = 'vat_number';
                        }
                    }

                    if ($existingCustomer) {
                        // Cliente esiste già: aggiorna con i dati del vecchio CRM e imposta legacy_id
                        // IMPORTANTE: Questo mantiene i dati del nuovo CRM ma aggiunge legacy_id per le relazioni
                        dump("  → Cliente duplicato trovato (legacy_id: {$legacyId}) tramite {$deduplicationMethod}");
                        dump("    Cliente esistente ID: {$existingCustomer->id} | Nome: {$existingCustomer->name} {$existingCustomer->last_name} | Business: {$existingCustomer->business_name}");
                        dump("    Aggiornando e impostando legacy_id = {$legacyId}");
                        
                        // Rimuovi created_at e updated_at dall'array per non sovrascrivere le date originali del customer esistente
                        $updateData = $data;
                        unset($updateData['created_at'], $updateData['updated_at']);
                        
                        $existingCustomer->legacy_id = $legacyId;
                        $existingCustomer->update($updateData);
                        $newCustomer = $existingCustomer;
                    } else {
                        // Cliente non esiste: crea normalmente con legacy_id
                        $newCustomer = Customer::create($data);
                        dump("  → Cliente creato nuovo (legacy_id: {$legacyId}, nuovo ID: {$newCustomer->id})");
                    }

                    // Aggiorna sempre il mapping nella tabella temporanea con il nuovo ID
                    mysqli_query($mysqli, "UPDATE datatype_clientis SET new_id = {$newCustomer->id} WHERE id = {$legacyId}");
                } catch (\Exception $e) {
                    dump('Cannot create customer legacy_id ' . $customer['id'] . ' Error: ' . substr($e->getMessage(), 0, 120) . '...');
                }
            }
            
            $offset += $batchSize;
            $result->free(); // Libera la memoria del risultato
        } while ($offset < $totCustomers);
    }
}
