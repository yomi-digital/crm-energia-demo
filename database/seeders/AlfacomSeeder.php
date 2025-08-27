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
        Alfacom\CalendarSeeder,
        Alfacom\ProductSeeder,
        Alfacom\FeebandSeeder,
        Alfacom\PaperworkSeeder;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Imposta MySQL come connessione di default per Laravel (bypass dell'env in generale)
        //config(['database.default' => 'mysql']); <-- Sblocca se localmente usi sqlite e non vuoi cambiare l'env. (runtime only)
        
        // Migrazioni se si crea un db nuovo per il trasferimento ed è vuoto
        /* dump('Creating tables in alfacom database...');
        \Artisan::call('migrate', ['--force' => true]); */
        
        $conn = new \mysqli(env('DB_HOST', 'localhost'), env('DB_USERNAME', 'root'), env('DB_PASSWORD', ''), '', env('DB_PORT', '3306'), env('DB_SOCKET', ''));

        // Crea il database alfacom se non esiste
        mysqli_query($conn, 'CREATE DATABASE IF NOT EXISTS alfacom CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        $tmpDb = 'alfacom_import';
        mysqli_query($conn, 'DROP DATABASE IF EXISTS ' . $tmpDb);
        mysqli_query($conn, 'CREATE DATABASE ' . $tmpDb . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        mysqli_select_db($conn, $tmpDb);

        // Alcune tabelle Alfacom, sono sprovviste di primary key, quindi disabilitiamo l'obbligo per permettere l'import
        mysqli_query($conn, 'SET @old_sql_require_primary_key := @@SESSION.sql_require_primary_key;');
        mysqli_query($conn, 'SET SESSION sql_require_primary_key = 0;');

        mysqli_query($conn, 'SET @old_sql_mode := @@sql_mode ;');
        mysqli_query($conn, 'SET @new_sql_mode := @old_sql_mode ;');
        mysqli_query($conn, "SET @new_sql_mode := TRIM(BOTH ',' FROM REPLACE(CONCAT(',',@new_sql_mode,','),',NO_ZERO_DATE,'  ,','));");
        mysqli_query($conn, "SET @new_sql_mode := TRIM(BOTH ',' FROM REPLACE(CONCAT(',',@new_sql_mode,','),',NO_ZERO_IN_DATE,',','));");
        mysqli_query($conn, "SET @@sql_mode := @new_sql_mode ;");

        // Lettura RAM indipendente dal file SQL, per evitare crash su pc con poca RAM php o di sistema
        try {
            $query = '';
            $handle = fopen(storage_path('imports/dump.sql'), 'r'); // Lettura del file SQL senza RAM overflow
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    $startWith = substr(trim($line), 0 ,2);
                    $endWith = substr(trim($line), -1 ,1);

                    if (empty($line) || $startWith == '--' || $startWith == '/*' || $startWith == '//') {
                        continue;
                    }

                    // Conversione MyISAM in InnoDB on-the-fly 
                    $line = str_replace('ENGINE=MyISAM', 'ENGINE=InnoDB', $line);

                    $query = $query . $line;
                    if ($endWith == ';') {
                        mysqli_query($conn, $query);
                        $query= '';
                    }
                }
                fclose($handle);
            }
            // mysqli_query($conn, "SET @@sql_mode := @old_sql_mode ;");
        } catch (\Exception $e) {
            // mysqli_query($conn, "SET @@sql_mode := @old_sql_mode ;");
            throw $e;
        } finally {
            // Ripristina l'impostazione originale della primary key (sempre eseguito)
            mysqli_query($conn, 'SET SESSION sql_require_primary_key = @old_sql_require_primary_key;');
        } 
        
       dump('Seeding Roles');
       $this->roles();
       dump('Seeding Brands');
       $this->brands($conn);
       dump('Seeding Users');
       $this->users($conn);
       dump('Seeding Customers');
       $this->customers($conn); 

        // dump('Seeding Mandates');
        // $this->mandates($conn);
        dump('Seeding Calendar');
        $this->calendar($conn);
        // dump('Seeding Links');
        // $this->links($conn);
        dump('Seeding Products');
        $this->products($conn);
        dump('Seeding Paperworks');
        $this->paperworks($conn);
        // dump('Seeding Feebands');
        $this->feebands($conn);

        dump('Cleanup');
        mysqli_query($conn, 'DROP DATABASE IF EXISTS ' . $tmpDb);
        mysqli_close($conn);

        // Drop unnecessary columns
        \Schema::table('paperworks', function ($table) {
            $table->dropColumn('legacy_id');
        });
        \Schema::table('products', function ($table) {
            $table->dropColumn('legacy_id');
        });
        \Schema::table('brands', function ($table) {
            $table->dropColumn('legacy_id');
        });
        \Schema::table('customers', function ($table) {
            $table->dropColumn('legacy_id');
        });
        \Schema::table('users', function ($table) {
            $table->dropColumn('legacy_id');
        });
    }
}
