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
        Schema::create('ai_paperworks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('filepath');
            $table->longText('extracted_text')->nullable();
            $table->longText('prompt_input')->nullable();
            $table->longText('prompt_output')->nullable();
            $table->longText('ai_extracted_customer')->nullable();
            $table->longText('ai_extracted_paperwork')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_paperworks');
    }
};
