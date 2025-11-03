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
        Schema::table('tipologie_tetto', function (Blueprint $table) {
            $table->renameColumn('costo_extra', 'costo_extra_kwp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipologie_tetto', function (Blueprint $table) {
            $table->renameColumn('costo_extra_kwp', 'costo_extra');
        });
    }
};
