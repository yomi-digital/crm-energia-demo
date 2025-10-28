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
        Schema::create('dettaglio_business_plan', function (Blueprint $table) {
            $table->id('id_bp');
            $table->unsignedBigInteger('fk_preventivo')->nullable()->index();
            $table->integer('anno_simulazione')->nullable();
            $table->float('costo_annuo_investimento')->nullable();
            $table->float('costo_annuo_assicurazione')->nullable();
            $table->float('costo_annuo_manutenzione')->nullable();
            $table->float('ricavo_risparmio_bolletta')->nullable();
            $table->float('ricavo_vendita_eccedenze')->nullable();
            $table->float('ricavo_incentivo_cer')->nullable();
            $table->float('ricavo_fondo_perduto')->nullable();
            $table->float('flusso_cassa_annuo')->nullable();
            $table->float('flusso_cassa_cumulato')->nullable();
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
        Schema::dropIfExists('dettaglio_business_plan');
    }
};
