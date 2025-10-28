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
        Schema::create('consumi_preventivo', function (Blueprint $table) {
            $table->id('id_consumo');
            $table->unsignedBigInteger('fk_preventivo')->nullable()->index();
            $table->string('anno_partenza')->nullable();
            $table->string('mese_partenza')->nullable();
            $table->float('costo_kwh_bolletta_medio')->nullable();
            $table->float('costo_kwh_bolletta_totale')->nullable();
            $table->float('totale_consumo_annuo')->nullable();
            $table->float('totale_costi_annui')->nullable();
            $table->string('tipologia_bolletta')->nullable();
            $table->longText('dettagli_consumo_json')->nullable();
            $table->float('consumo_diurno_annuo')->nullable();
            $table->float('consumo_notturno_annuo')->nullable();
            $table->float('capacita_batteria_consigliata')->nullable();
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
        Schema::dropIfExists('consumi_preventivo');
    }
};
