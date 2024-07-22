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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->string('name');
            $table->unsignedBigInteger('brand_id')->nullable()->index();
            $table->float('price')->nullable();
            $table->text('notes')->nullable();
            $table->integer('discount_percent')->nullable();
            $table->string('fee_type')->default('FISSO');
            $table->float('management_fee')->nullable();
            $table->float('getter_fee')->nullable();
            $table->float('agent_fee')->nullable();
            $table->float('structure_fee')->nullable();
            $table->float('salesperson_fee')->nullable();
            $table->float('structure_top_fee')->nullable();
            $table->text('fees')->nullable();
            $table->unsignedTinyInteger('enabled')->default(1);
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
