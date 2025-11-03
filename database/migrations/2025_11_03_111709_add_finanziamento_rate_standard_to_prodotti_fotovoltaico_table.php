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
            $table->longText('finanziamento_rate_standard')->nullable()->after('prezzo_base');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prodotti_fotovoltaico', function (Blueprint $table) {
            $table->dropColumn('finanziamento_rate_standard');
        });
    }
};
