<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('job_listings', function (Blueprint $t) {
      $t->id();
      $t->unsignedBigInteger('employer_id');
      $t->string('title');
      $t->text('description')->nullable();
      $t->integer('positions')->default(1);
      $t->date('start_date')->nullable();
      $t->date('end_date')->nullable();
      $t->string('status')->default('open');
      $t->timestamps();

      $t->foreign('employer_id')->references('id')->on('employers')->onDelete('cascade');
    });
  }

  public function down(): void {
    Schema::dropIfExists('job_listings');
  }
};
