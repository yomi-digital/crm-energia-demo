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
        Schema::table('dettagli_prodotto_preventivo', function (Blueprint $table) {
            $table->dropIndex('dettagli_prodotto_preventivo_fk_prodotto_index');
            $table->dropConstrainedForeignId('fk_prodotto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dettagli_prodotto_preventivo', function (Blueprint $table) {
            $table->unsignedBigInteger('fk_prodotto')->nullable()->after('fk_preventivo');
            $table->foreign('fk_prodotto')->references('id')->on('prodotti');
        });
    }
};
