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
            // Aggiunge la colonna incentivo_pod dopo incentivo_dedicated
            $table->decimal('incentivo_pod', 8, 2)->nullable()->after('incentivo_dedicated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incentivi', function (Blueprint $table) {
            // Rimuove la colonna incentivo_pod
            $table->dropColumn('incentivo_pod');
        });
    }
};
