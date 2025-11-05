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
        Schema::table('paperworks', function (Blueprint $table) {
            $table->longText('transfers_history')->nullable()->after('owner_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paperworks', function (Blueprint $table) {
            $table->dropColumn('transfers_history');
        });
    }
};
