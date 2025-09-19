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
            // Rinomina la colonna incentivo in incentivo_cer
            $table->renameColumn('incentivo', 'incentivo_cer');
            
            // Aggiunge la nuova colonna incentivo_dedicated
            $table->decimal('incentivo_dedicated', 8, 2)->nullable()->after('incentivo_cer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incentivi', function (Blueprint $table) {
            // Rimuove la colonna incentivo_dedicated
            $table->dropColumn('incentivo_dedicated');
            
            // Rinomina incentivo_cer di nuovo in incentivo
            $table->renameColumn('incentivo_cer', 'incentivo');
        });
    }
};
