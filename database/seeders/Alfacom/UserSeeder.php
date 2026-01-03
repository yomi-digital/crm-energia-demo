<?php

namespace Database\Seeders\Alfacom;

use App\Models\User;
use Carbon\Carbon;

trait UserSeeder
{
    private function users($mysqli)
    {
        $testAdmin = User::create([
            'name' => 'Test User',
            'email' => 'admin@alfacom.com',
            'password' => bcrypt('pas_sw&8o?.t6rd'),
        ]);
        $testAdmin->assignRole('gestione');
        // $testGestione = User::create([
        //     'name' => 'Gestione',
        //     'email' => 'gestione@alfacom.com',
        //     'password' => bcrypt('password'),
        // ]);
        // $testGestione->assignRole('gestione');
        // $testBackoffice = User::create([
        //     'name' => 'Backoffice',
        //     'email' => 'backoffice@alfacom.com',
        //     'password' => bcrypt('password'),
        // ]);
        // $testBackoffice->assignRole('backoffice');
        // $testAgente = User::create([
        //     'name' => 'Agente',
        //     'email' => 'agente@alfacom.com',
        //     'password' => bcrypt('password'),
        // ]);
        // $testAgente->assignRole('agente');
        // $testStruttura = User::create([
        //     'name' => 'Struttura',
        //     'email' => 'struttura@alfacom.com',
        //     'password' => bcrypt('password'),
        // ]);
        // $testStruttura->assignRole('struttura');
        // $testTelemarketing = User::create([
        //     'name' => 'Telemarketing',
        //     'email' => 'telemarketing@alfacom.com',
        //     'password' => bcrypt('password'),
        // ]);
        // $testTelemarketing->assignRole('telemarketing');
        // $testTeamleader = User::create([
        //     'name' => 'Team Leader',
        //     'email' => 'teamleader@alfacom.com',
        //     'password' => bcrypt('password'),
        // ]);
        // $testTeamleader->assignRole('team leader');
        // $testAmministrazione = User::create([
        //     'name' => 'Amministrazione',
        //     'email' => 'amministrazione@alfacom.com',
        //     'password' => bcrypt('password'),
        // ]);
        // $testAmministrazione->assignRole('amministrazione');

        mysqli_query($mysqli, 'ALTER TABLE datatype_accounts ADD new_id BIGINT');
        $legacyUsers = $mysqli->query("SELECT * FROM datatype_accounts")->fetch_all(MYSQLI_ASSOC);
        $totUsers = count($legacyUsers);
        dump('Total users: ' . $totUsers);
        $countI = 0;
        foreach ($legacyUsers as $user) {
            $countI++;
            dump('Processing user ' . $countI . ' of ' . $totUsers);
            if (! $user['email']) {
                if (! $user['username']) {
                    $user['username'] = \Str::random(10);
                }
                $user['email'] = $user['username'] . '@dummy.email';
            }
            // Check if there is more than one user with the same email
            $filtered = array_filter($legacyUsers, function ($u) use ($user) {
                return strtolower($u['email']) === strtolower($user['email']);
            });
            if (count($filtered) > 1) {
                $parts = explode('@', $user['email']);
                $user['email'] = $parts[0] . '+' . $user['username'] . '@' . $parts[1];
            }
            try {
                // Prepara la data di creazione originale dal database legacy
                $createdAt = null;
                $updatedAt = null;
                
                $dateField = $user['data_inserimento'] ?? $user['created_at'] ?? null;
                if (isset($dateField) && $dateField !== '0000-00-00' && $dateField !== null && $dateField !== '') {
                    try {
                        $createdAt = Carbon::parse($dateField);
                        $updatedAt = $createdAt;
                    } catch (\Exception $e) {
                        $createdAt = null;
                        $updatedAt = null;
                    }
                }

                $normalizedArea = $this->normalizeArea($user['area']);
                $data = [
                    'legacy_id' => $user['id'],
                    'name' => $user['nome'],
                    'last_name' => $user['cognome'],
                    'email' => $user['email'],
                    // 'email_verified_at' => $user[],
                    'password' => $user['password'],
                    'agent_code' => $user['codice_agente'],
                    'manager_id' => $user['id_capoarea'],
                    // 'structure_id' => $user['id_struttura'],
                    'commercial_profile' => $user['profilo_commerciale'],
                    'area' => $normalizedArea,
                    'team_leader' => $user['team_leader'] === 'SI' ? 1 : 0,
                    'extractor' => $user['estrattore'] === 'SI' ? 1 : 0,
                    'enabled' => $user['abilitato'] === 'SI' ? 1 : 0,
                    // 'last_login_at' => $user[],
                    // 'last_logout_at' => $user[],
                ];

                // Imposta created_at e updated_at se disponibili
                if ($createdAt) {
                    $data['created_at'] = $createdAt;
                    $data['updated_at'] = $updatedAt;
                }

                $newUser = User::create($data);
                mysqli_query($mysqli, "UPDATE datatype_accounts SET new_id = {$newUser->id} WHERE id = {$user['id']}");
                if ($user['livello'] === 'NULL') {
                    dump('Cannot attach role to legacy_id ' . $user['id']);
                    continue;
                }
                $newUser->assignRole($user['livello']);
            } catch (\Exception $e) {
                dump('Cannot create legacy_id ' . $user['id'] . ' Error: ' . substr($e->getMessage(), 0, 120) . '...');
            }
        }

        /**
         * STOP HERE. Associations will be done manually.
         */
        return;

        // Associate manager id, structure id, agents
        foreach ($legacyUsers as $row) {
            $user = User::where('legacy_id', $row['id'])->first();
            if (! $user) {
                dump('User not found legacy_id ' . $row['id']);
                continue;
            }
            if (is_numeric($row['id_capoarea'])) {
                $manager = \App\Models\Manager::where('legacy_id', $row['id_capoarea'])->first();
                if ($manager) {
                    $user->manager_id = $manager->id;
                }
            }
            // if (is_numeric($row['id_struttura'])) {
            //     $structure = \App\Models\Structure::where('legacy_id', $row['id_struttura'])->first();
            //     if ($structure) {
            //         $user->structure_id = $structure->id;
            //     }
            // }

            if ($user->hasRole('struttura')) {
                if ($row['agenti_assegnati'] && strlen($row['agenti_assegnati'])) {
                    $agentsArray = explode(',', $row['agenti_assegnati']);
                    if (count($agentsArray)) {
                        // dump('exploded agents: ' . count($agentsArray));
                        $agents = User::whereIn('legacy_id', $agentsArray)->get();
                        dump('Found agents: ' . count($agents) . ' of ' . count($agentsArray));
                        foreach ($agents as $agent) {
                            if ($user->id === $agent->id) {
                                continue;
                            }
                            \App\Models\UserRelationship::create([
                                'user_id' => $user->id,
                                'related_id' => $agent->id,
                                'role' => $agent->roles()->first()->name,
                            ]);
                        }
                    }
                }
            }

            if ($user->hasRole('team leader')) {
                if ($row['operatori_assegnati'] && strlen($row['operatori_assegnati'])) {
                    $telemarketingArray = explode(',', $row['operatori_assegnati']);
                    if (count($telemarketingArray)) {
                        // dump('exploded agents: ' . count($agentsArray));
                        $telemarketings = User::whereIn('legacy_id', $telemarketingArray)->get();
                        dump('Found telemarketing: ' . count($telemarketings) . ' of ' . count($telemarketingArray));
                        foreach ($telemarketings as $tlm) {
                            if ($user->id === $tlm->id) {
                                continue;
                            }
                            \App\Models\UserRelationship::create([
                                'user_id' => $user->id,
                                'related_id' => $tlm->id,
                                'role' => $tlm->roles()->first()->name,
                            ]);
                        }
                    }
                }
            }


            $user->save();
        }
    }

    /**
     * Normalizza il valore area: solo "Catania" o "Lecce".
     * Default a "Catania" se vuoto o non valido.
     */
    private function normalizeArea($area): string
    {
        // Gestione valori non stringa, null, undefined o stringhe vuote
        if (! is_string($area) || trim($area) === '') {
            return 'Catania';
        }

        $cleaned = ucfirst(strtolower(trim($area)));
        $allowed = ['Catania', 'Lecce'];

        if (! in_array($cleaned, $allowed, true)) {
            return 'Catania';
        }

        return $cleaned;
    }
}
