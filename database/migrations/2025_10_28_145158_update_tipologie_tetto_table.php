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
            $table->renameColumn('nome_voce', 'nome_tipologia');
            $table->dropColumn(['tipo_voce', 'tipo_valore', 'valore_default', 'anni_durata_default']);
            $table->text('note')->nullable()->after('nome_tipologia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipologie_tetto', function (Blueprint $table) {
            $table->renameColumn('nome_tipologia', 'nome_voce');
            $table->string('tipo_voce');
            $table->string('tipo_valore');
            $table->float('valore_default')->nullable();
            $table->integer('anni_durata_default')->nullable();
            $table->dropColumn('note');
        });
    }
};
