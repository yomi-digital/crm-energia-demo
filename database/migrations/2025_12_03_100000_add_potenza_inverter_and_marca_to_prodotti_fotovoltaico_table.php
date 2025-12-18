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
            $table->float('potenza_inverter')->default(0)->after('capacita_kwh');
            $table->string('marca')->default('Marca')->after('potenza_inverter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prodotti_fotovoltaico', function (Blueprint $table) {
            $table->dropColumn(['potenza_inverter', 'marca']);
        });
    }
};

