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
        Schema::create('feebands', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->index();
            $table->timestamp('start_date');
            $table->timestamp('end_date')->nullable();
            $table->string('fee_type')->nullable();
            $table->float('management_fee')->nullable();
            $table->float('getter_fee')->nullable();
            $table->float('agent_fee')->nullable();
            $table->float('structure_fee')->nullable();
            $table->float('salesperson_fee')->nullable();
            $table->float('structure_top_fee')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feebands');
    }
};
