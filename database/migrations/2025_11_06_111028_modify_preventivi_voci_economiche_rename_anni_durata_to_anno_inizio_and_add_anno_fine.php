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
        Schema::table('preventivi_voci_economiche', function (Blueprint $table) {
            // Rinomina anni_durata_agevolazione_salvata in anno_inizio_salvato
            $table->renameColumn('anni_durata_agevolazione_salvata', 'anno_inizio_salvato');
            
            // Aggiungi la colonna anno_fine_salvato
            $table->float('anno_fine_salvato')->nullable()->after('anno_inizio_salvato');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('preventivi_voci_economiche', function (Blueprint $table) {
            // Rimuovi la colonna anno_fine_salvato
            $table->dropColumn('anno_fine_salvato');
            
            // Rinomina anno_inizio_salvato in anni_durata_agevolazione_salvata
            $table->renameColumn('anno_inizio_salvato', 'anni_durata_agevolazione_salvata');
        });
    }
};
