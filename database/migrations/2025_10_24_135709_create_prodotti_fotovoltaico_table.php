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
        Schema::create('prodotti_fotovoltaico', function (Blueprint $table) {
            $table->id('id_prodotto');
            $table->unsignedBigInteger('fk_categoria')->nullable()->index();
            $table->string('codice_prodotto');
            $table->string('descrizione');
            $table->float('potenza_kwp')->nullable();
            $table->float('capacita_kwh')->nullable();
            $table->float('prezzo_base')->nullable();
            $table->timestamps();

            $table->foreign('fk_categoria')
                  ->references('id_categoria')
                  ->on('categorie_prodotto_fotovoltaico')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prodotti_fotovoltaico');
    }
};
