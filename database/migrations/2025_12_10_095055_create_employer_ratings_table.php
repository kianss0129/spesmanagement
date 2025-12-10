<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('employer_ratings', function (Blueprint $t) {
      $t->id();
      $t->unsignedBigInteger('employer_id');
      $t->unsignedBigInteger('beneficiary_id');
      $t->unsignedBigInteger('application_id')->nullable();
      $t->tinyInteger('punctuality')->nullable();
      $t->tinyInteger('work_attitude')->nullable();
      $t->tinyInteger('output_quality')->nullable();
      $t->tinyInteger('communication')->nullable();
      $t->tinyInteger('overall')->nullable();
      $t->text('comment')->nullable();
      $t->timestamps();
    });
  }

  public function down(): void {
    Schema::dropIfExists('employer_ratings');
  }
};
