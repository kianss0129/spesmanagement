<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('attendances', function (Blueprint $t) {
      $t->id();
      $t->unsignedBigInteger('beneficiary_id');
      $t->unsignedBigInteger('employer_id')->nullable();
      $t->date('date');
      $t->time('time_in')->nullable();
      $t->time('time_out')->nullable();
      $t->text('notes')->nullable();
      $t->timestamps();
    });
  }

  public function down(): void {
    Schema::dropIfExists('attendances');
  }
};
