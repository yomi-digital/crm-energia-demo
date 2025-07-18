<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AlfacomSqliteSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            \Database\Seeders\AlfacomSqlite\RoleSeeder::class,
            \Database\Seeders\AlfacomSqlite\UserSeeder::class,
            \Database\Seeders\AlfacomSqlite\BrandSeeder::class,
            \Database\Seeders\AlfacomSqlite\ProductSeeder::class,
            \Database\Seeders\AlfacomSqlite\CustomerSeeder::class,
            \Database\Seeders\AlfacomSqlite\CalendarSeeder::class,
            \Database\Seeders\AlfacomSqlite\FeebandSeeder::class,
            \Database\Seeders\AlfacomSqlite\LinkSeeder::class,
            \Database\Seeders\AlfacomSqlite\MandateSeeder::class,
            \Database\Seeders\AlfacomSqlite\PaperworkSeeder::class,
        ]);
    }
} 
