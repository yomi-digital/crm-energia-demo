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
        Schema::create('applicabilita_voce', function (Blueprint $table) {
            $table->unsignedBigInteger('fk_voce');
            $table->string('tipo_cliente');
            $table->timestamps();

            $table->primary(['fk_voce', 'tipo_cliente']);

            $table->foreign('fk_voce')
                  ->references('id_voce')
                  ->on('voci_economiche')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicabilita_voce');
    }
};
