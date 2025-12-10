<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('beneficiaries', function (Blueprint $table) {
      $table->id();
      $table->string('student_id')->unique()->nullable();
      $table->string('first_name');
      $table->string('last_name');
      $table->string('email')->nullable();
      $table->string('phone')->nullable();
      $table->unsignedBigInteger('school_id')->nullable();
      $table->json('documents')->nullable();
      $table->timestamps();
    });
  }

  public function down(): void {
    Schema::dropIfExists('beneficiaries');
  }
};
