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
        // Prima converti i dati: true -> 22, false -> 0
        DB::statement('UPDATE voci_economiche SET iva = CASE WHEN iva = 1 THEN 22 ELSE 0 END');
        
        // Poi modifica la colonna da boolean a integer
        Schema::table('voci_economiche', function (Blueprint $table) {
            $table->integer('iva')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Converti i dati: 22 -> true (1), 0 -> false (0), altri valori -> false
        DB::statement('UPDATE voci_economiche SET iva = CASE WHEN iva = 22 THEN 1 ELSE 0 END');
        
        // Poi modifica la colonna da integer a boolean
        Schema::table('voci_economiche', function (Blueprint $table) {
            $table->boolean('iva')->default(false)->change();
        });
    }
};
