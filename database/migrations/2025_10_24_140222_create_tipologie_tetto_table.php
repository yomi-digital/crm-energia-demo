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
        Schema::create('tipologie_tetto', function (Blueprint $table) {
            $table->id('id_voce');
            $table->string('nome_voce');
            $table->string('tipo_voce');
            $table->string('tipo_valore');
            $table->float('valore_default')->nullable();
            $table->integer('anni_durata_default')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipologie_tetto');
    }
};
