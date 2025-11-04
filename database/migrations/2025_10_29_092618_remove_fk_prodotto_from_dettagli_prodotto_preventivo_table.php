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
            // Elimina prima la foreign key
            $table->dropForeign(['fk_prodotto']);
            // Poi elimina l'indice (potrebbe essere già stato eliminato automaticamente, ma lo lasciamo per sicurezza)
            $table->dropIndex('dettagli_prodotto_preventivo_fk_prodotto_index');
            // Infine elimina la colonna
            $table->dropColumn('fk_prodotto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dettagli_prodotto_preventivo', function (Blueprint $table) {
            $table->unsignedBigInteger('fk_prodotto')->nullable()->after('fk_preventivo');
            $table->foreign('fk_prodotto')->references('id_prodotto')->on('prodotti_fotovoltaico')->onDelete('set null');
        });
    }
};
