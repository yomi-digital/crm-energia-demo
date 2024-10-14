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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paperwork_id')->nullable();
            $table->unsignedBigInteger('created_by')->index();
            $table->string('title');
            $table->text('description');
            $table->unsignedSmallInteger('status')->default(1); // 1: open, 2: in progress, 3: closed
            $table->unsignedSmallInteger('priority')->default(1);
            $table->timestamp('closed_at')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
