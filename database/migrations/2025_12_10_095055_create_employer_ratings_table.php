<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('employer_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employer_id');
            $table->unsignedBigInteger('beneficiary_id');
            $table->unsignedBigInteger('application_id')->nullable();
            $table->tinyInteger('punctuality')->nullable();
            $table->tinyInteger('work_attitude')->nullable();
            $table->tinyInteger('output_quality')->nullable();
            $table->tinyInteger('communication')->nullable();
            $table->tinyInteger('overall')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            // Foreign key to employers table (ratings are against employer profiles)
            $table->foreign('employer_id')->references('id')->on('employers')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('employer_ratings');
    }
};
