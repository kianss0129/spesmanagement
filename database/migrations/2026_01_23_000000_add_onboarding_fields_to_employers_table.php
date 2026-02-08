<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
{
    Schema::table('employers', function (Blueprint $table) {

        if (!Schema::hasColumn('employers', 'documents')) {
            $table->json('documents')->nullable()->after('address');
        }

        if (!Schema::hasColumn('employers', 'approval_status')) {
            $table->string('approval_status')->default('pending')->after('approved');
        }

        if (!Schema::hasColumn('employers', 'rejection_reason')) {
            $table->string('rejection_reason')->nullable()->after('approval_status');
        }

        if (!Schema::hasColumn('employers', 'rejected_at')) {
            $table->timestamp('rejected_at')->nullable()->after('rejection_reason');
        }

        if (!Schema::hasColumn('employers', 'approved_by')) {
            $table->unsignedBigInteger('approved_by')->nullable()->after('rejected_at');
        }

        if (!Schema::hasColumn('employers', 'approved_at')) {
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        }

        if (!Schema::hasColumn('employers', 'status')) {
            $table->string('status')->nullable()->after('approved_at');
        }
    });
}


    public function down(): void
    {
        Schema::table('employers', function (Blueprint $table) {
            $table->dropColumn([
                'documents',
                'approval_status',
                'rejection_reason',
                'rejected_at',
                'approved_by',
                'approved_at',
                'status',
            ]);
        });
    }
};
