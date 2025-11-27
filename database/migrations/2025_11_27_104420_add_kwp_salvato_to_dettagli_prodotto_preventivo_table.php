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
            $table->float('kWp_salvato')->nullable()->after('capacita_batteria_salvata');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dettagli_prodotto_preventivo', function (Blueprint $table) {
            $table->dropColumn('kWp_salvato');
        });
    }
};
