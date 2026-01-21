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
            $table->boolean('shipping')->default(false)->after('indirizzo_installazione');
            $table->boolean('visura')->default(false)->after('shipping');
            $table->text('other')->nullable()->after('visura');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paperworks', function (Blueprint $table) {
            $table->dropColumn(['shipping', 'visura', 'other']);
        });
    }
};
