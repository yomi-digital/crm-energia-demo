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
        Schema::table('dettaglio_business_plan', function (Blueprint $table) {
            $table->float('incentivo_pnnr')->nullable()->after('ricavo_fondo_perduto');
            $table->float('detrazione_fiscale')->nullable()->after('incentivo_pnnr');
            $table->float('sconto')->nullable()->after('detrazione_fiscale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dettaglio_business_plan', function (Blueprint $table) {
            $table->dropColumn(['incentivo_pnnr', 'detrazione_fiscale', 'sconto']);
        });
    }
};
