<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::table('beneficiaries', function (Blueprint $table) {
        $table->timestamp('onboarding_completed_at')->nullable()->after('approved');
    });
}

public function down(): void
{
    Schema::table('beneficiaries', function (Blueprint $table) {
        $table->dropColumn('onboarding_completed_at');
    });
}

};
