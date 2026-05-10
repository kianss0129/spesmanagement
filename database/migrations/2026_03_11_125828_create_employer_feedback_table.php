<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employer_feedback', function (Blueprint $table) {

            $table->id();

            $table->foreignId('employer_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->text('message');

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employer_feedback');
    }
};