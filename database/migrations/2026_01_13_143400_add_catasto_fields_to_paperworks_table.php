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
            if (!Schema::hasColumn('paperworks', 'catasto')) {
                $table->string('catasto')->nullable();
            }
            if (!Schema::hasColumn('paperworks', 'foglio')) {
                $table->string('foglio')->nullable();
            }
            if (!Schema::hasColumn('paperworks', 'particella')) {
                $table->string('particella')->nullable();
            }
            if (!Schema::hasColumn('paperworks', 'sub')) {
                $table->string('sub')->nullable();
            }
            if (!Schema::hasColumn('paperworks', 'indirizzo_installazione')) {
                $table->text('indirizzo_installazione')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paperworks', function (Blueprint $table) {
            $columnsToDrop = [];
            
            if (Schema::hasColumn('paperworks', 'catasto')) {
                $columnsToDrop[] = 'catasto';
            }
            if (Schema::hasColumn('paperworks', 'foglio')) {
                $columnsToDrop[] = 'foglio';
            }
            if (Schema::hasColumn('paperworks', 'particella')) {
                $columnsToDrop[] = 'particella';
            }
            if (Schema::hasColumn('paperworks', 'sub')) {
                $columnsToDrop[] = 'sub';
            }
            if (Schema::hasColumn('paperworks', 'indirizzo_installazione')) {
                $columnsToDrop[] = 'indirizzo_installazione';
            }
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
