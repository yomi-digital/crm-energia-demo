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
        Schema::create('dettagli_prodotto_preventivo', function (Blueprint $table) {
            $table->id('id_dettaglio');
            $table->unsignedBigInteger('fk_preventivo')->nullable()->index();
            $table->unsignedBigInteger('fk_prodotto')->nullable()->index();
            $table->string('nome_prodotto_salvato')->nullable();
            $table->string('categoria_prodotto_salvata')->nullable();
            $table->float('quantita')->nullable();
            $table->float('prezzo_unitario_salvato')->nullable();
            $table->float('capacita_batteria_salvata')->nullable();
            $table->timestamps();

            $table->foreign('fk_preventivo')
                  ->references('id_preventivo')
                  ->on('preventivi')
                  ->onDelete('set null');

            $table->foreign('fk_prodotto')
                  ->references('id_prodotto')
                  ->on('prodotti_fotovoltaico')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dettagli_prodotto_preventivo');
    }
};
