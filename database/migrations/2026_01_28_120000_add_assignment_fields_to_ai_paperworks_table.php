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
        Schema::table('ai_paperworks', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_backoffice_id')->nullable()->after('brand_id');
            $table->string('assignment_status')->nullable()->after('assigned_backoffice_id');
            $table->dateTime('assignment_expires_at')->nullable()->after('assignment_status');

            $table->foreign('assigned_backoffice_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_paperworks', function (Blueprint $table) {
            $table->dropForeign(['assigned_backoffice_id']);
            $table->dropColumn(['assigned_backoffice_id', 'assignment_status', 'assignment_expires_at']);
        });
    }
};

