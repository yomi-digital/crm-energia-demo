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
        Schema::create('report_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('report_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('parent')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->string('agent')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->string('brand')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product')->nullable();
            $table->string('order_code')->nullable();
            $table->unsignedBigInteger('paperwork_id')->nullable();
            $table->date('inserted_at')->nullable();
            $table->date('activated_at')->nullable();
            $table->string('status')->nullable();
            $table->float('payout')->nullable();
            $table->float('payout_confirmed')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_entries');
    }
};
