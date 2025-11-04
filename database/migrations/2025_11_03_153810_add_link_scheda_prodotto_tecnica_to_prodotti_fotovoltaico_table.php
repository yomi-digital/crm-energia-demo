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
        Schema::table('prodotti_fotovoltaico', function (Blueprint $table) {
            $table->string('link_scheda_prodotto_tecnica')->nullable()->after('finanziamento_rate_standard');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prodotti_fotovoltaico', function (Blueprint $table) {
            $table->dropColumn('link_scheda_prodotto_tecnica');
        });
    }
};
