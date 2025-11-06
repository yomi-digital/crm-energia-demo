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
        Schema::table('voci_economiche', function (Blueprint $table) {
            // Rinomina anni_durata_default in anno_inizio
            $table->renameColumn('anni_durata_default', 'anno_inizio');
            
            // Aggiungi la colonna anno_fine
            $table->integer('anno_fine')->nullable()->after('anno_inizio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('voci_economiche', function (Blueprint $table) {
            // Rimuovi la colonna anno_fine
            $table->dropColumn('anno_fine');
            
            // Rinomina anno_inizio in anni_durata_default
            $table->renameColumn('anno_inizio', 'anni_durata_default');
        });
    }
};
