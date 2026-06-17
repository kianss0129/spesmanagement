<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('beneficiaries', 'completed_at')) {
            Schema::table('beneficiaries', function (Blueprint $table) {
                $table->timestamp('completed_at')->nullable()->after('onboarding_completed_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('beneficiaries', 'completed_at')) {
            Schema::table('beneficiaries', function (Blueprint $table) {
                $table->dropColumn('completed_at');
            });
        }
    }
};
