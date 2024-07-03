<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AlfacomSeeder extends Seeder
{
    use Alfacom\RoleSeeder,
        Alfacom\UserSeeder,
        Alfacom\MandateSeeder,
        Alfacom\CustomerSeeder,
        Alfacom\BrandSeeder,
        Alfacom\LinkSeeder,
        Alfacom\DocumentSeeder,
        Alfacom\CalendarSeeder,
        Alfacom\ProductSeeder,
        Alfacom\PaperworkSeeder;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conn = new \mysqli('localhost', env('DB_USERNAME', 'root'), env('DB_PASSWORD', ''), '', env('DB_PORT', '3306'), env('DB_SOCKET', ''));

        $tmpDb = 'alfacom_import';
        mysqli_query($conn, 'DROP DATABASE IF EXISTS ' . $tmpDb);
        mysqli_query($conn, 'CREATE DATABASE ' . $tmpDb);
        mysqli_select_db($conn, $tmpDb);

        mysqli_query($conn, 'SET @old_sql_mode := @@sql_mode ;');
        mysqli_query($conn, 'SET @new_sql_mode := @old_sql_mode ;');
        mysqli_query($conn, "SET @new_sql_mode := TRIM(BOTH ',' FROM REPLACE(CONCAT(',',@new_sql_mode,','),',NO_ZERO_DATE,'  ,','));");
        mysqli_query($conn, "SET @new_sql_mode := TRIM(BOTH ',' FROM REPLACE(CONCAT(',',@new_sql_mode,','),',NO_ZERO_IN_DATE,',','));");
        mysqli_query($conn, "SET @@sql_mode := @new_sql_mode ;");

        try {
            $query = '';
            $sqlScript = file(storage_path('imports/dump.sql'));
            foreach ($sqlScript as $line) {
                $startWith = substr(trim($line), 0 ,2);
                $endWith = substr(trim($line), -1 ,1);

                if (empty($line) || $startWith == '--' || $startWith == '/*' || $startWith == '//') {
                    continue;
                }

                $query = $query . $line;
                if ($endWith == ';') {
                    mysqli_query($conn, $query);
                    $query= '';
                }
            }
            // mysqli_query($conn, "SET @@sql_mode := @old_sql_mode ;");
        } catch (\Exception $e) {
            // mysqli_query($conn, "SET @@sql_mode := @old_sql_mode ;");
            throw $e;
        }

        $this->roles();
        $this->users($conn);
        $this->mandates($conn);
        $this->brands($conn);
        $this->customers($conn);
        $this->calendar($conn);
        $this->links($conn);
        $this->documents($conn);
        $this->products($conn);
        $this->paperworks($conn);
        mysqli_query($conn, 'DROP DATABASE IF EXISTS ' . $tmpDb);
        mysqli_close($conn);
    }
}
