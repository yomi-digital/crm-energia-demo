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
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable()->index();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->unsignedBigInteger('mandate_id')->nullable()->index();
            $table->text('account_pod_pdr')->nullable();
            $table->double('annual_consumption')->nullable();
            $table->string('contract_type')->nullable();
            $table->string('category')->nullable();
            $table->string('type')->nullable();
            $table->string('energy_type')->nullable();
            $table->string('mobile_type')->nullable();
            $table->string('coverage')->nullable();
            $table->string('previous_provider')->nullable();
            $table->text('notes')->nullable();
            $table->text('owner_notes')->nullable();
            $table->string('order_code')->nullable();
            $table->string('order_status')->nullable();
            $table->date('partner_sent_at')->nullable();
            $table->string('partner_outcome')->nullable();
            $table->date('partner_outcome_at')->nullable();
            $table->unsignedTinyInteger('paid')->nullable();
            $table->date('canceled_at')->nullable();
            $table->date('expired_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->unsignedBigInteger('confirmed_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
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
