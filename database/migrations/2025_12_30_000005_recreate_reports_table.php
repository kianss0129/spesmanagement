<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Recreate reports table to support both medical reports and employer reports
        if (Schema::hasTable('reports')) {
            Schema::drop('reports');
        }

        Schema::create('reports', function (Blueprint $t) {
            $t->id();
            $t->foreignId('patient_id')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('doctor_id')->nullable()->constrained('users')->nullOnDelete();

            // Employer-oriented fields
            $t->unsignedBigInteger('employer_id')->nullable();
            $t->string('title')->nullable();
            $t->text('body')->nullable();
            $t->string('file_path')->nullable();

            // Legacy medical fields kept for compatibility
            $t->string('report_type')->nullable();
            $t->text('report_details')->nullable();
            $t->date('report_date')->nullable();

            $t->timestamps();

            $t->foreign('employer_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');

        // Recreate original schema (best-effort)
        Schema::create('reports', function (Blueprint $t) {
            $t->id();
            $t->foreignId('patient_id')->constrained('users');
            $t->foreignId('doctor_id')->constrained('users');
            $t->string('report_type');
            $t->text('report_details');
            $t->date('report_date');
            $t->timestamps();
        });
    }
};
