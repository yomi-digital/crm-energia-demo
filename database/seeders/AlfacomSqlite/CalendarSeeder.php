<?php

namespace Database\Seeders\AlfacomSqlite;

use App\Models\Calendar;
use App\Models\User;
use Illuminate\Database\Seeder;

class CalendarSeeder extends Seeder
{
    public function run()
    {
        $this->createAppointments();
    }

    private function createAppointments()
    {
        $users = User::all();
        $agents = $users->whereIn('agent_code', ['AG001', 'AG002']);

        $appointments = [
            [
                'title' => 'Presentazione offerta energia - Mario Rossi',
                'notes_call_center' => 'Cliente interessato a offerta luce verde',
                'notes_agent' => 'Portare materiale informativo ENEL',
                'notes' => 'Appuntamento confermato via telefono',
                'status' => 'Confermato',
                'referent' => 'Mario Rossi',
                'user_name' => 'Mario Rossi',
                'user_phone' => '02 1234567',
                'user_mobile' => '333 1234567',
                'user_address' => 'Via Roma 123',
                'user_city' => 'Milano',
                'type' => 'Presentazione',
                'cost' => 0,
                'start' => '2024-03-15 10:00:00',
                'end' => '2024-03-15 11:00:00',
                'all_day' => false
            ],
            [
                'title' => 'Firma contratto dual fuel - Azienda Srl',
                'notes_call_center' => 'Cliente business, necessita documentazione completa',
                'notes_agent' => 'Portare contratti e documenti aziendali',
                'notes' => 'Appuntamento per firma contratto luce e gas',
                'status' => 'Confermato',
                'referent' => 'Marco Ferrari',
                'user_name' => 'Azienda Srl',
                'user_phone' => '02 1111111',
                'user_mobile' => '333 1111111',
                'user_address' => 'Via delle Industrie 100',
                'user_city' => 'Milano',
                'type' => 'Firma Contratto',
                'cost' => 0,
                'start' => '2024-03-16 14:00:00',
                'end' => '2024-03-16 15:30:00',
                'all_day' => false
            ],
            [
                'title' => 'Verifica installazione fibra - Giulia Bianchi',
                'notes_call_center' => 'Cliente ha problemi con connessione',
                'notes_agent' => 'Verificare stato installazione e configurazione',
                'notes' => 'Supporto tecnico per problemi di connessione',
                'status' => 'In Attesa',
                'referent' => 'Giulia Bianchi',
                'user_name' => 'Giulia Bianchi',
                'user_phone' => '06 9876543',
                'user_mobile' => '333 9876543',
                'user_address' => 'Via del Corso 456',
                'user_city' => 'Roma',
                'type' => 'Supporto Tecnico',
                'cost' => 0,
                'start' => '2024-03-17 09:00:00',
                'end' => '2024-03-17 10:00:00',
                'all_day' => false
            ],
            [
                'title' => 'Presentazione offerta business - Impresa Spa',
                'notes_call_center' => 'Cliente interessato a soluzioni business complete',
                'notes_agent' => 'Preparare presentazione pacchetti business',
                'notes' => 'Appuntamento per presentazione soluzioni aziendali',
                'status' => 'Confermato',
                'referent' => 'Sofia Russo',
                'user_name' => 'Impresa Spa',
                'user_phone' => '06 2222222',
                'user_mobile' => '333 2222222',
                'user_address' => 'Via del Commercio 200',
                'user_city' => 'Roma',
                'type' => 'Presentazione',
                'cost' => 0,
                'start' => '2024-03-18 11:00:00',
                'end' => '2024-03-18 12:30:00',
                'all_day' => false
            ],
            [
                'title' => 'Rinnovo contratto energia - Luca Verdi',
                'notes_call_center' => 'Cliente vuole rinnovare contratto energia',
                'notes_agent' => 'Presentare nuove offerte e condizioni',
                'notes' => 'Appuntamento per rinnovo contratto energia elettrica',
                'status' => 'Confermato',
                'referent' => 'Luca Verdi',
                'user_name' => 'Luca Verdi',
                'user_phone' => '011 5555555',
                'user_mobile' => '333 5555555',
                'user_address' => 'Corso Vittorio Emanuele 789',
                'user_city' => 'Torino',
                'type' => 'Rinnovo',
                'cost' => 0,
                'start' => '2024-03-19 15:00:00',
                'end' => '2024-03-19 16:00:00',
                'all_day' => false
            ],
            [
                'title' => 'Installazione contatore smart - Anna Neri',
                'notes_call_center' => 'Cliente ha richiesto installazione contatore smart',
                'notes_agent' => 'Coordinare con tecnico installazione',
                'notes' => 'Installazione contatore intelligente per monitoraggio consumi',
                'status' => 'Programmato',
                'referent' => 'Anna Neri',
                'user_name' => 'Anna Neri',
                'user_phone' => '055 7777777',
                'user_mobile' => '333 7777777',
                'user_address' => 'Via dei Calzaiuoli 321',
                'user_city' => 'Firenze',
                'type' => 'Installazione',
                'cost' => 50,
                'start' => '2024-03-20 08:00:00',
                'end' => '2024-03-20 10:00:00',
                'all_day' => false
            ],
            [
                'title' => 'Presentazione offerta mobile - Paolo Gialli',
                'notes_call_center' => 'Cliente interessato a piano mobile business',
                'notes_agent' => 'Portare sim card e materiale promozionale',
                'notes' => 'Presentazione piano mobile per uso aziendale',
                'status' => 'Confermato',
                'referent' => 'Paolo Gialli',
                'user_name' => 'Paolo Gialli',
                'user_phone' => '051 9999999',
                'user_mobile' => '333 9999999',
                'user_address' => 'Via dell\'Indipendenza 654',
                'user_city' => 'Bologna',
                'type' => 'Presentazione',
                'cost' => 0,
                'start' => '2024-03-21 13:00:00',
                'end' => '2024-03-21 14:00:00',
                'all_day' => false
            ]
        ];

        foreach ($appointments as $appointmentData) {
            $agent = $agents->random();
            $createdBy = $users->where('agent_code', 'BO001')->first();

            Calendar::firstOrCreate([
                'title' => $appointmentData['title'],
                'start' => $appointmentData['start']
            ], array_merge($appointmentData, [
                'agent_id' => $agent ? $agent->id : null,
                'created_by' => $createdBy ? $createdBy->id : null
            ]));
        }
    }
} 
