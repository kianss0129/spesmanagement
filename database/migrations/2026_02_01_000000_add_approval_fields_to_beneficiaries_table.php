<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            if (! Schema::hasColumn('beneficiaries', 'approval_status')) {
                $table->string('approval_status')->default('pending');
            }

            if (! Schema::hasColumn('beneficiaries', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }

            if (! Schema::hasColumn('beneficiaries', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }

            if (! Schema::hasColumn('beneficiaries', 'resubmit_at')) {
                $table->timestamp('resubmit_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            if (Schema::hasColumn('beneficiaries', 'resubmit_at')) {
                $table->dropColumn('resubmit_at');
            }

            if (Schema::hasColumn('beneficiaries', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }

            if (Schema::hasColumn('beneficiaries', 'approved_at')) {
                $table->dropColumn('approved_at');
            }

            if (Schema::hasColumn('beneficiaries', 'approval_status')) {
                $table->dropColumn('approval_status');
            }
        });
    }
};
