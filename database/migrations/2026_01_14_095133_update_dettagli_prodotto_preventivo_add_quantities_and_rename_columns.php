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
        Schema::table('dettagli_prodotto_preventivo', function (Blueprint $table) {
            // Inverter
            $table->renameColumn('marca_salvata', 'marca_inverter_salvata');
            $table->integer('quantita_inverter_salvati')->nullable()->after('marca_inverter_salvata');

            // Batteria
            $table->integer('quantita_batterie_salvate')->nullable()->after('capacita_batteria_salvata');
            $table->float('potenza_batterie_salvato')->nullable()->after('quantita_batterie_salvate');
            $table->string('marca_batteria_salvato')->nullable()->after('potenza_batterie_salvato');

            // Pannelli
            $table->string('marca_pannelli_salvato')->nullable()->after('kWp_salvato');
            $table->integer('quantita_pannelli_salvato')->nullable()->after('marca_pannelli_salvato');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dettagli_prodotto_preventivo', function (Blueprint $table) {
            // Inverter
            $table->renameColumn('marca_inverter_salvata', 'marca_salvata');
            $table->dropColumn('quantita_inverter_salvati');

            // Batteria
            $table->dropColumn('quantita_batterie_salvate');
            $table->dropColumn('potenza_batterie_salvato');
            $table->dropColumn('marca_batteria_salvato');

            // Pannelli
            $table->dropColumn('marca_pannelli_salvato');
            $table->dropColumn('quantita_pannelli_salvato');
        });
    }
};
