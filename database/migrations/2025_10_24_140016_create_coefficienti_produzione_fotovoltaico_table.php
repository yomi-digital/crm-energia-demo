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
        Schema::create('coefficienti_produzione_fotovoltaico', function (Blueprint $table) {
            $table->id('id_coeff');
            $table->string('area_geografica');
            $table->string('esposizione');
            $table->float('coefficiente_kwh_kwp');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coefficienti_produzione_fotovoltaico');
    }
};
