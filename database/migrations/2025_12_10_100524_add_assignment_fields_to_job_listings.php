<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_beneficiary_id')->nullable()->after('slots');
            $table->enum('employer_choice', ['approved', 'rejected', 'pending'])->default('pending')->after('assigned_beneficiary_id');

            // Foreign key for assigned beneficiary
            $table->foreign('assigned_beneficiary_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            // Only drop foreign key if column exists
            if (Schema::hasColumn('job_listings', 'assigned_beneficiary_id')) {
                $table->dropForeign(['assigned_beneficiary_id']);
                $table->dropColumn('assigned_beneficiary_id');
            }

            if (Schema::hasColumn('job_listings', 'employer_choice')) {
                $table->dropColumn('employer_choice');
            }
        });
    }
};
