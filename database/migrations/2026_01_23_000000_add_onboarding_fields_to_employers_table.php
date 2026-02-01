<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employers', function (Blueprint $table) {
            // Documents JSON
            $table->json('documents')->nullable()->after('address');

            // Workflow status
            $table->string('approval_status')->default('pending')->after('approved');

            // Optional reason for rejection
            $table->string('rejection_reason')->nullable()->after('approval_status');
            $table->timestamp('rejected_at')->nullable()->after('rejection_reason');

            // Who approved and when
            $table->unsignedBigInteger('approved_by')->nullable()->after('rejected_at');
            $table->timestamp('approved_at')->nullable()->after('approved_by');

            // Optional status column (if needed)
            $table->string('status')->nullable()->after('approved_at');
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
