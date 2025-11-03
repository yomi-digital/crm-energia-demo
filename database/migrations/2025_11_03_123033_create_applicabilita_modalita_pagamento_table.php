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
        Schema::create('applicabilita_modalita_pagamento', function (Blueprint $table) {
            $table->unsignedBigInteger('fk_modalita');
            $table->string('tipo_cliente');
            $table->timestamps();

            $table->primary(['fk_modalita', 'tipo_cliente']);

            $table->foreign('fk_modalita')
                  ->references('id_modalita')
                  ->on('modalita_pagamento')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicabilita_modalita_pagamento');
    }
};
