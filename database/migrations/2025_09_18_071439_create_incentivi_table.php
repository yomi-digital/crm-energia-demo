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
        Schema::create('incentivi', function (Blueprint $table) {
            $table->id();
            $table->string('periodoBolletta');
            $table->float('prezzoMedioKwh');
            $table->float('spesaBollettaMensile');
            $table->string('hasPanels');
            $table->string('citta');
            $table->string('email');
            $table->string('nominativo');
            $table->string('numeroDiTelefono');
            $table->boolean('privacyAccepted');
            $table->string('provincia');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incentivi');
    }
};
