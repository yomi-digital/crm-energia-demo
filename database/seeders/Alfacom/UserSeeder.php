<?php

namespace Database\Seeders\Alfacom;

use App\Models\User;

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
        foreach ($legacyUsers as $user) {
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
                $newUser = User::create([
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
                    'area' => $user['area'],
                    'team_leader' => $user['team_leader'] === 'SI' ? 1 : 0,
                    'extractor' => $user['estrattore'] === 'SI' ? 1 : 0,
                    'enabled' => $user['abilitato'] === 'SI' ? 1 : 0,
                    // 'last_login_at' => $user[],
                    // 'last_logout_at' => $user[],
                ]);
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
}
