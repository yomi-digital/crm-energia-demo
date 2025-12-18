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
            $table->float('potenza_inverter')->default(0)->after('kWp_salvato');
            $table->string('marca')->default('Marca')->after('potenza_inverter');
            $table->tinyInteger('iva')->default(1)->after('marca');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dettagli_prodotto_preventivo', function (Blueprint $table) {
            $table->dropColumn(['potenza_inverter', 'marca', 'iva']);
        });
    }
};

