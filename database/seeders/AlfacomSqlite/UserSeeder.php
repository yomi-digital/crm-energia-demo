<?php

namespace Database\Seeders\AlfacomSqlite;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $this->createTestUsers();
        $this->createSampleUsers();
    }

    private function createTestUsers()
    {
        // Admin principale
        $testAdmin = User::firstOrCreate([
            'email' => 'admin@alfacom.com'
        ], [
            'name' => 'Test User',
            'password' => bcrypt('pas_sw&8o?.t6rd'),
        ]);
        $testAdmin->assignRole('gestione');

        // Altri utenti di test per diversi ruoli
        $testUsers = [
            [
                'name' => 'Gestione',
                'email' => 'gestione@alfacom.com',
                'password' => bcrypt('password'),
                'role' => 'gestione'
            ],
            [
                'name' => 'Backoffice',
                'email' => 'backoffice@alfacom.com',
                'password' => bcrypt('password'),
                'role' => 'backoffice'
            ],
            [
                'name' => 'Agente',
                'email' => 'agente@alfacom.com',
                'password' => bcrypt('password'),
                'role' => 'agente'
            ],
            [
                'name' => 'Struttura',
                'email' => 'struttura@alfacom.com',
                'password' => bcrypt('password'),
                'role' => 'struttura'
            ],
            [
                'name' => 'Telemarketing',
                'email' => 'telemarketing@alfacom.com',
                'password' => bcrypt('password'),
                'role' => 'telemarketing'
            ],
            [
                'name' => 'Team Leader',
                'email' => 'teamleader@alfacom.com',
                'password' => bcrypt('password'),
                'role' => 'team leader'
            ],
            [
                'name' => 'Amministrazione',
                'email' => 'amministrazione@alfacom.com',
                'password' => bcrypt('password'),
                'role' => 'amministrazione'
            ]
        ];

        foreach ($testUsers as $userData) {
            $user = User::firstOrCreate([
                'email' => $userData['email']
            ], [
                'name' => $userData['name'],
                'password' => $userData['password'],
            ]);
            $user->assignRole($userData['role']);
        }
    }

    private function createSampleUsers()
    {
        // Creo alcuni utenti di esempio per simulare un ambiente realistico
        $sampleUsers = [
            [
                'name' => 'Mario Rossi',
                'email' => 'mario.rossi@alfacom.com',
                'password' => bcrypt('password'),
                'role' => 'agente',
                'agent_code' => 'AG001',
                'commercial_profile' => 'Energia',
                'area' => 'Lombardia',
                'team_leader' => 0,
                'extractor' => 0,
                'enabled' => 1
            ],
            [
                'name' => 'Giulia Bianchi',
                'email' => 'giulia.bianchi@alfacom.com',
                'password' => bcrypt('password'),
                'role' => 'agente',
                'agent_code' => 'AG002',
                'commercial_profile' => 'Telefonia',
                'area' => 'Lazio',
                'team_leader' => 0,
                'extractor' => 0,
                'enabled' => 1
            ],
            [
                'name' => 'Luca Verdi',
                'email' => 'luca.verdi@alfacom.com',
                'password' => bcrypt('password'),
                'role' => 'struttura',
                'agent_code' => 'ST001',
                'commercial_profile' => 'Energia',
                'area' => 'Piemonte',
                'team_leader' => 0,
                'extractor' => 1,
                'enabled' => 1
            ],
            [
                'name' => 'Anna Neri',
                'email' => 'anna.neri@alfacom.com',
                'password' => bcrypt('password'),
                'role' => 'telemarketing',
                'agent_code' => 'TL001',
                'commercial_profile' => 'Telefonia',
                'area' => 'Toscana',
                'team_leader' => 1,
                'extractor' => 0,
                'enabled' => 1
            ],
            [
                'name' => 'Paolo Gialli',
                'email' => 'paolo.gialli@alfacom.com',
                'password' => bcrypt('password'),
                'role' => 'backoffice',
                'agent_code' => 'BO001',
                'commercial_profile' => 'Amministrativo',
                'area' => 'Sede Centrale',
                'team_leader' => 0,
                'extractor' => 0,
                'enabled' => 1
            ]
        ];

        foreach ($sampleUsers as $userData) {
            $role = $userData['role'];
            unset($userData['role']);
            
            $user = User::firstOrCreate([
                'email' => $userData['email']
            ], $userData);
            
            $user->assignRole($role);
        }
    }
} 
