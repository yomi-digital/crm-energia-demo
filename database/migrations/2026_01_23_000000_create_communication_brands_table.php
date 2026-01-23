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
        Schema::create('communication_brands', function (Blueprint $table) {
            $table->id();

            // Riferimento alla comunicazione
            $table->unsignedBigInteger('id_communication');

            // Riferimento al brand
            $table->unsignedBigInteger('id_brand');

            $table->timestamps();

            // Foreign keys (assumendo nomi tabelle esistenti)
            $table->foreign('id_communication')
                ->references('id')
                ->on('communications')
                ->onDelete('cascade');

            $table->foreign('id_brand')
                ->references('id')
                ->on('brands')
                ->onDelete('cascade');

            // Evita duplicati della stessa coppia comunicazione/brand
            $table->unique(['id_communication', 'id_brand']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communication_brands');
    }
};

