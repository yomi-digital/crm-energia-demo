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
            $table->tinyInteger('is_default')->default(0);
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->string('fee_type')->nullable();
            $table->float('management_fee')->nullable();
            $table->float('top_partner_fee')->nullable();
            $table->float('top_fee')->nullable();
            $table->float('partner_fee')->nullable();
            $table->float('smart_fee')->nullable();
            $table->float('collaborator_fee')->nullable();
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
