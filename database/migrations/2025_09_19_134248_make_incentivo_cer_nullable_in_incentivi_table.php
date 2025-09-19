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
        Schema::table('incentivi', function (Blueprint $table) {
            // Rende la colonna incentivo_cer nullable
            $table->decimal('incentivo_cer', 8, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incentivi', function (Blueprint $table) {
            // Rimuove il nullable dalla colonna incentivo_cer
            $table->decimal('incentivo_cer', 8, 2)->nullable(false)->change();
        });
    }
};
