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
        Schema::create('listini', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->text('descrizione')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('listino_prodotto_fotovoltaico', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fk_listino');
            $table->unsignedBigInteger('fk_prodotto');
            $table->timestamps();

            $table->foreign('fk_listino')->references('id')->on('listini')->onDelete('cascade');
            $table->foreign('fk_prodotto')->references('id_prodotto')->on('prodotti_fotovoltaico')->onDelete('cascade');
            
            // Prevent duplicate pairs
            $table->unique(['fk_listino', 'fk_prodotto'], 'listino_prodotto_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listino_prodotto_fotovoltaico');
        Schema::dropIfExists('listini');
    }
};
