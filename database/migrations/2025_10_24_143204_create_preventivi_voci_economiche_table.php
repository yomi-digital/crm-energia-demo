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
        Schema::create('preventivi_voci_economiche', function (Blueprint $table) {
            $table->id('id_dettaglio');
            $table->unsignedBigInteger('fk_preventivo')->nullable()->index();
            $table->string('nome_voce_salvato')->nullable();
            $table->string('tipo_voce_salvata')->nullable();
            $table->float('valore_applicato')->nullable();
            $table->string('tipo_valore_salvato')->nullable();
            $table->float('anni_durata_agevolazione_salvata')->nullable();
            $table->timestamps();

            $table->foreign('fk_preventivo')
                  ->references('id_preventivo')
                  ->on('preventivi')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preventivi_voci_economiche');
    }
};
