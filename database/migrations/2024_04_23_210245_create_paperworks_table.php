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
        Schema::create('paperworks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('account_pod_pdr')->nullable();
            $table->date('added_at')->nullable();
            $table->string('order_status')->nullable();
            $table->date('partner_outcome_at')->nullable();
            $table->text('owner_notes')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->string('contract_type')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->text('notes')->nullable();
            $table->string('partner_outcome')->nullable();
            $table->string('order_code')->nullable();
            $table->unsignedTinyInteger('paid')->nullable();
            $table->unsignedTinyInteger('pda')->nullable();
            $table->unsignedTinyInteger('appointment')->nullable();
            $table->unsignedBigInteger('mandate_id')->nullable();
            $table->string('coverage')->nullable();
            $table->date('canceled_at')->nullable();
            $table->date('expired_at')->nullable();
            $table->double('annual_consumption')->nullable();
            $table->string('category')->nullable();
            $table->unsignedTinyInteger('confirmed')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->unsignedBigInteger('confirmed_by')->nullable();
            $table->string('type')->nullable();
            $table->string('energy_type')->nullable();
            $table->unsignedBigInteger('brand_category_id')->nullable();
            $table->text('pdf_url')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paperworks');
    }
};
