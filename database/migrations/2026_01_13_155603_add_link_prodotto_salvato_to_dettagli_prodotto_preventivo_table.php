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
        Schema::table('dettagli_prodotto_preventivo', function (Blueprint $table) {
            $table->string('link_prodotto_salvato')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dettagli_prodotto_preventivo', function (Blueprint $table) {
            $table->dropColumn('link_prodotto_salvato');
        });
    }
};
