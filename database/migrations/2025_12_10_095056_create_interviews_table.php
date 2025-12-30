<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('interviews', function (Blueprint $table) {
            $table->id();

            // Reference job_listings table and proper employer/beneficiary tables
            $table->unsignedBigInteger('job_listing_id')->nullable();
            $table->unsignedBigInteger('employer_id')->nullable();
            $table->unsignedBigInteger('beneficiary_id')->nullable();

            // Allow linking back to application when available
            $table->unsignedBigInteger('application_id')->nullable();

            $table->dateTime('scheduled_at');
            $table->string('meet_link')->nullable();
            $table->string('calendar_event_id')->nullable();

            $table->enum('status', ['scheduled','completed','cancelled'])->default('scheduled');

            $table->timestamps();

            // Add foreign key constraints where the referenced tables exist in this schema
            if (Schema::hasTable('job_listings')) {
                $table->foreign('job_listing_id')->references('id')->on('job_listings')->onDelete('set null');
            }
            if (Schema::hasTable('employers')) {
                $table->foreign('employer_id')->references('id')->on('employers')->onDelete('set null');
            }
            if (Schema::hasTable('beneficiaries')) {
                $table->foreign('beneficiary_id')->references('id')->on('beneficiaries')->onDelete('set null');
            }
        });
    }

    public function down(): void {
        Schema::dropIfExists('interviews');
    }
};
