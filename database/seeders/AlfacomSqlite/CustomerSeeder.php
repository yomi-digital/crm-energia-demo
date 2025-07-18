<?php

namespace Database\Seeders\AlfacomSqlite;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run()
    {
        $this->createResidentialCustomers();
        $this->createBusinessCustomers();
    }

    private function createResidentialCustomers()
    {
        $residentialCustomers = [
            [
                'name' => 'Mario',
                'last_name' => 'Rossi',
                'email' => 'mario.rossi@email.com',
                'phone' => '02 1234567',
                'mobile' => '333 1234567',
                'business_name' => null,
                'tax_id_code' => 'RSSMRA80A01H501U',
                'vat_number' => null,
                'ateco_code' => null,
                'pec' => null,
                'unique_code' => null,
                'category' => 'Residenziale',
                'address' => 'Via Roma 123',
                'region' => 'LOMBARDIA',
                'province' => 'MI',
                'city' => 'MILANO',
                'zip' => '20100',
                'added_at' => '2024-01-15',
                'confirmed_at' => '2024-01-16'
            ],
            [
                'name' => 'Giulia',
                'last_name' => 'Bianchi',
                'email' => 'giulia.bianchi@email.com',
                'phone' => '06 9876543',
                'mobile' => '333 9876543',
                'business_name' => null,
                'tax_id_code' => 'BNCGLI85B02H501V',
                'vat_number' => null,
                'ateco_code' => null,
                'pec' => null,
                'unique_code' => null,
                'category' => 'Residenziale',
                'address' => 'Via del Corso 456',
                'region' => 'LAZIO',
                'province' => 'RM',
                'city' => 'ROMA',
                'zip' => '00100',
                'added_at' => '2024-01-20',
                'confirmed_at' => '2024-01-21'
            ],
            [
                'name' => 'Luca',
                'last_name' => 'Verdi',
                'email' => 'luca.verdi@email.com',
                'phone' => '011 5555555',
                'mobile' => '333 5555555',
                'business_name' => null,
                'tax_id_code' => 'VRDLCU90C03H501W',
                'vat_number' => null,
                'ateco_code' => null,
                'pec' => null,
                'unique_code' => null,
                'category' => 'Residenziale',
                'address' => 'Corso Vittorio Emanuele 789',
                'region' => 'PIEMONTE',
                'province' => 'TO',
                'city' => 'TORINO',
                'zip' => '10100',
                'added_at' => '2024-02-01',
                'confirmed_at' => '2024-02-02'
            ],
            [
                'name' => 'Anna',
                'last_name' => 'Neri',
                'email' => 'anna.neri@email.com',
                'phone' => '055 7777777',
                'mobile' => '333 7777777',
                'business_name' => null,
                'tax_id_code' => 'NREANN88D04H501X',
                'vat_number' => null,
                'ateco_code' => null,
                'pec' => null,
                'unique_code' => null,
                'category' => 'Residenziale',
                'address' => 'Via dei Calzaiuoli 321',
                'region' => 'TOSCANA',
                'province' => 'FI',
                'city' => 'FIRENZE',
                'zip' => '50100',
                'added_at' => '2024-02-10',
                'confirmed_at' => '2024-02-11'
            ],
            [
                'name' => 'Paolo',
                'last_name' => 'Gialli',
                'email' => 'paolo.gialli@email.com',
                'phone' => '051 9999999',
                'mobile' => '333 9999999',
                'business_name' => null,
                'tax_id_code' => 'GLLPLA92E05H501Y',
                'vat_number' => null,
                'ateco_code' => null,
                'pec' => null,
                'unique_code' => null,
                'category' => 'Residenziale',
                'address' => 'Via dell\'Indipendenza 654',
                'region' => 'EMILIA-ROMAGNA',
                'province' => 'BO',
                'city' => 'BOLOGNA',
                'zip' => '40100',
                'added_at' => '2024-02-15',
                'confirmed_at' => '2024-02-16'
            ]
        ];

        $users = User::all();
        $agent = $users->where('agent_code', 'AG001')->first();

        foreach ($residentialCustomers as $customerData) {
            $customer = Customer::firstOrCreate([
                'email' => $customerData['email']
            ], array_merge($customerData, [
                'added_by' => $agent ? $agent->id : null,
                'confirmed_by' => $agent ? $agent->id : null
            ]));
        }
    }

    private function createBusinessCustomers()
    {
        $businessCustomers = [
            [
                'name' => 'Marco',
                'last_name' => 'Ferrari',
                'email' => 'marco.ferrari@azienda1.it',
                'phone' => '02 1111111',
                'mobile' => '333 1111111',
                'business_name' => 'Azienda Srl',
                'tax_id_code' => 'FRRMRC75F06H501Z',
                'vat_number' => '12345678901',
                'ateco_code' => '62.01.00',
                'pec' => 'azienda1@pec.it',
                'unique_code' => 'ABC12345',
                'category' => 'Business',
                'address' => 'Via delle Industrie 100',
                'region' => 'LOMBARDIA',
                'province' => 'MI',
                'city' => 'MILANO',
                'zip' => '20100',
                'added_at' => '2024-01-25',
                'confirmed_at' => '2024-01-26'
            ],
            [
                'name' => 'Sofia',
                'last_name' => 'Russo',
                'email' => 'sofia.russo@azienda2.it',
                'phone' => '06 2222222',
                'mobile' => '333 2222222',
                'business_name' => 'Impresa Spa',
                'tax_id_code' => 'RSSSFI82G07H501A',
                'vat_number' => '98765432109',
                'ateco_code' => '47.11.00',
                'pec' => 'azienda2@pec.it',
                'unique_code' => 'DEF67890',
                'category' => 'Business',
                'address' => 'Via del Commercio 200',
                'region' => 'LAZIO',
                'province' => 'RM',
                'city' => 'ROMA',
                'zip' => '00100',
                'added_at' => '2024-02-05',
                'confirmed_at' => '2024-02-06'
            ],
            [
                'name' => 'Roberto',
                'last_name' => 'Esposito',
                'email' => 'roberto.esposito@azienda3.it',
                'phone' => '011 3333333',
                'mobile' => '333 3333333',
                'business_name' => 'Industria Srl',
                'tax_id_code' => 'SPTRBT78H08H501B',
                'vat_number' => '45678912345',
                'ateco_code' => '25.11.00',
                'pec' => 'azienda3@pec.it',
                'unique_code' => 'GHI13579',
                'category' => 'Business',
                'address' => 'Via della Produzione 300',
                'region' => 'PIEMONTE',
                'province' => 'TO',
                'city' => 'TORINO',
                'zip' => '10100',
                'added_at' => '2024-02-12',
                'confirmed_at' => '2024-02-13'
            ],
            [
                'name' => 'Elena',
                'last_name' => 'Colombo',
                'email' => 'elena.colombo@azienda4.it',
                'phone' => '055 4444444',
                'mobile' => '333 4444444',
                'business_name' => 'Servizi Spa',
                'tax_id_code' => 'CLMELN85I09H501C',
                'vat_number' => '78912345678',
                'ateco_code' => '69.20.00',
                'pec' => 'azienda4@pec.it',
                'unique_code' => 'JKL24680',
                'category' => 'Business',
                'address' => 'Via dei Servizi 400',
                'region' => 'TOSCANA',
                'province' => 'FI',
                'city' => 'FIRENZE',
                'zip' => '50100',
                'added_at' => '2024-02-18',
                'confirmed_at' => '2024-02-19'
            ],
            [
                'name' => 'Alessandro',
                'last_name' => 'Ricci',
                'email' => 'alessandro.ricci@azienda5.it',
                'phone' => '051 5555555',
                'mobile' => '333 5555555',
                'business_name' => 'Logistica Srl',
                'tax_id_code' => 'RCCLSN80J10H501D',
                'vat_number' => '32165498765',
                'ateco_code' => '52.21.00',
                'pec' => 'azienda5@pec.it',
                'unique_code' => 'MNO35791',
                'category' => 'Business',
                'address' => 'Via della Logistica 500',
                'region' => 'EMILIA-ROMAGNA',
                'province' => 'BO',
                'city' => 'BOLOGNA',
                'zip' => '40100',
                'added_at' => '2024-02-20',
                'confirmed_at' => '2024-02-21'
            ]
        ];

        $users = User::all();
        $agent = $users->where('agent_code', 'AG002')->first();

        foreach ($businessCustomers as $customerData) {
            $customer = Customer::firstOrCreate([
                'email' => $customerData['email']
            ], array_merge($customerData, [
                'added_by' => $agent ? $agent->id : null,
                'confirmed_by' => $agent ? $agent->id : null
            ]));
        }
    }
} 
