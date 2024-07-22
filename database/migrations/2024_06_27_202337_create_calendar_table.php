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
        Schema::create('calendar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id')->nullable()->index();
            $table->string('title')->nullable();
            $table->text('notes_call_center')->nullable();
            $table->text('notes_agent')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->nullable();
            $table->string('referent')->nullable();
            $table->unsignedBigInteger('user_connection')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->string('user_name')->nullable();
            $table->string('user_phone')->nullable();
            $table->string('user_mobile')->nullable();
            $table->string('user_address')->nullable();
            $table->string('user_city')->nullable();
            $table->string('type')->nullable();
            $table->float('cost')->nullable();
            $table->timestamp('start')->nullable();
            $table->timestamp('end')->nullable();
            $table->unsignedTinyInteger('all_day')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar');
    }
};
