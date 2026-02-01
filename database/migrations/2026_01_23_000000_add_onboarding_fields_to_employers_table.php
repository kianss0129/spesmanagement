<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employers', function (Blueprint $table) {
            if (!Schema::hasColumn('employers', 'documents')) {
                $table->json('documents')->nullable();
            }

            if (!Schema::hasColumn('employers', 'approval_status')) {
                $table->string('approval_status')->default('pending'); // pending, approved, rejected
            }

            if (!Schema::hasColumn('employers', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('employers', function (Blueprint $table) {
            if (Schema::hasColumn('employers', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }

            if (Schema::hasColumn('employers', 'approval_status')) {
                $table->dropColumn('approval_status');
            }

            if (Schema::hasColumn('employers', 'documents')) {
                $table->dropColumn('documents');
            }
        });
    }
};
