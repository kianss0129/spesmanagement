<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('interviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();
            $table->foreignId('employer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('beneficiary_id')->constrained('users')->cascadeOnDelete();

            $table->dateTime('scheduled_at');
            $table->string('meet_link')->nullable();
            $table->string('calendar_event_id')->nullable();

            $table->enum('status', ['scheduled','completed','cancelled'])->default('scheduled');

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('interviews');
    }
};
