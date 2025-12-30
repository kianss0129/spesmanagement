<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('analytics', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // e.g., 'applicant_trend', 'attendance', 'completion', 'top_employers'
            $table->json('data'); // store chart data as JSON
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('analytics');
    }
};
