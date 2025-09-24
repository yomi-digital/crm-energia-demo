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
            // Aggiunge la colonna autoconsume_savings con default 0
            $table->decimal('autoconsume_savings', 10, 2)->default(0)->after('incentivo_pod');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incentivi', function (Blueprint $table) {
            // Rimuove la colonna autoconsume_savings
            $table->dropColumn('autoconsume_savings');
        });
    }
};
