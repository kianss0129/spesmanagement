<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // beneficiary
            $table->foreignId('employer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('draft'); 
            // draft | submitted | approved | rejected | onboarded
            $table->string('school')->nullable();
            $table->string('course')->nullable();
            $table->integer('year_level')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};