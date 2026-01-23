<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employers', function (Blueprint $table) {
            $table->id();

            // Link back to the users table
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            $table->string('company_name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();

            // Tracks when the employer completed onboarding
            $table->timestamp('onboarding_completed_at')->nullable();

            // Approval status (PESO)
            $table->boolean('approved')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employers');
    }
};
