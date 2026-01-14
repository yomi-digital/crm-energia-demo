<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('prodotti_fotovoltaico', function (Blueprint $table) {
            // Inverter
            $table->renameColumn('marca', 'marca_inverter');
            $table->integer('quantita_inverter')->nullable()->after('marca_inverter');

            // Batterie
            $table->string('marca_batteria')->nullable()->after('quantita_inverter');
            $table->float('potenza_batteria')->nullable()->after('marca_batteria');
            $table->integer('quantita_batterie')->nullable()->after('potenza_batteria');

            // Pannelli
            $table->integer('quantita_pannelli')->nullable()->after('potenza_kwp');
            $table->string('marca_pannelli')->nullable()->after('quantita_pannelli');
            $table->renameColumn('potenza_kwp', 'potenza_kwp_pannelli');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prodotti_fotovoltaico', function (Blueprint $table) {
            // Inverter
            $table->renameColumn('marca_inverter', 'marca');
            $table->dropColumn('quantita_inverter');

            // Batterie
            $table->dropColumn('marca_batteria');
            $table->dropColumn('potenza_batteria');
            $table->dropColumn('quantita_batterie');

            // Pannelli
            $table->dropColumn('quantita_pannelli');
            $table->dropColumn('marca_pannelli');
            $table->renameColumn('potenza_kwp_pannelli', 'potenza_kwp');
        });
    }
};

