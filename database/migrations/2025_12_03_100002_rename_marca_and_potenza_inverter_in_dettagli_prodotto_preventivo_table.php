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
            $table->renameColumn('marca', 'marca_salvata');
            $table->renameColumn('potenza_inverter', 'potenza_inverter_salvata');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dettagli_prodotto_preventivo', function (Blueprint $table) {
            $table->renameColumn('marca_salvata', 'marca');
            $table->renameColumn('potenza_inverter_salvata', 'potenza_inverter');
        });
    }
};

