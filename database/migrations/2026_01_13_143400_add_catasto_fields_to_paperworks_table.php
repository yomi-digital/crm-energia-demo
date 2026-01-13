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
            $table->string('catasto')->nullable();
            $table->string('foglio')->nullable();
            $table->string('particella')->nullable();
            $table->string('sub')->nullable();
            $table->text('indirizzo_installazione')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paperworks', function (Blueprint $table) {
            $table->dropColumn([
                'catasto',
                'foglio',
                'particella',
                'sub',
                'indirizzo_installazione',
            ]);
        });
    }
};
