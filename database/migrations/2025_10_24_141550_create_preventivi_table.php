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
        Schema::create('preventivi', function (Blueprint $table) {
            $table->id('id_preventivo');
            $table->string('numero_preventivo')->nullable();
            $table->date('data_preventivo')->nullable();
            $table->unsignedBigInteger('fk_cliente')->nullable()->index();
            $table->unsignedBigInteger('fk_agente')->nullable()->index();
            $table->string('stato')->nullable();
            $table->string('tetto_salvato')->nullable();
            $table->string('area_geografica_salvata')->nullable();
            $table->string('esposizione_salvata')->nullable();
            $table->string('modalita_pagamento_salvata')->nullable();
            $table->longText('bonifico_data_json')->nullable();
            $table->longText('finanziamento_data_json')->nullable();
            $table->string('opzione_manutenzione_salvata')->nullable();
            $table->float('costo_annuo_manutenzione_salvato')->nullable();
            $table->string('opzione_assicurazione_salvata')->nullable();
            $table->float('costo_annuo_assicurazione_salvato')->nullable();
            $table->string('pdf_url')->nullable();
            $table->float('produzione_annua_stimata')->nullable();
            $table->float('risparmio_autoconsumo_annuo')->nullable();
            $table->float('vendita_eccedenze_rid_annua')->nullable();
            $table->float('incentivo_cer_annuo')->nullable();
            $table->float('detrazione_fiscale_annua')->nullable();
            $table->timestamps();

            $table->foreign('fk_cliente')
                  ->references('id')
                  ->on('customers')
                  ->onDelete('set null');

            $table->foreign('fk_agente')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preventivi');
    }
};
