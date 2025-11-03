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
        Schema::table('consumi_preventivo', function (Blueprint $table) {
            $table->float('potenza_impianto_consigliata')->nullable()->after('capacita_batteria_consigliata');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consumi_preventivo', function (Blueprint $table) {
            $table->dropColumn('potenza_impianto_consigliata');
        });
    }
};
