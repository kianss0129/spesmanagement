<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('applications', function (Blueprint $t) {
      $t->id();
      $t->unsignedBigInteger('beneficiary_id');
      $t->unsignedBigInteger('job_listing_id');
      $t->string('status')->default('applied');
      $t->timestamps();

      $t->foreign('beneficiary_id')->references('id')->on('beneficiaries');
      $t->foreign('job_listing_id')->references('id')->on('job_listings');
    });
  }

  public function down(): void {
    Schema::dropIfExists('applications');
  }
};
