<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Cambia il default della colonna category da SEGNALAZIONE_GUASTO a ALTRO
            $table->string('category')->default('ALTRO')->change();
        });
        
        // Aggiorna tutti i record esistenti che hanno SEGNALAZIONE_GUASTO a ALTRO
        DB::table('tickets')->where('category', 'SEGNALAZIONE_GUASTO')->update(['category' => 'ALTRO']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Ripristina il default precedente
            $table->string('category')->default('SEGNALAZIONE_GUASTO')->change();
        });
    }
};
