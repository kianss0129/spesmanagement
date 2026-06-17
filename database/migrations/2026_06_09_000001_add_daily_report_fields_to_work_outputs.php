<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('work_outputs', function (Blueprint $table) {
            if (Schema::hasColumn('work_outputs', 'file_path')) {
                $table->string('file_path')->nullable()->change();
            }

            if (! Schema::hasColumn('work_outputs', 'application_id')) {
                $table->foreignId('application_id')->nullable()->after('beneficiary_id')->constrained('applications')->nullOnDelete();
            }

            if (! Schema::hasColumn('work_outputs', 'job_listing_id')) {
                $table->foreignId('job_listing_id')->nullable()->after('application_id')->constrained('job_listings')->nullOnDelete();
            }

            if (! Schema::hasColumn('work_outputs', 'work_date')) {
                $table->date('work_date')->nullable()->after('job_listing_id');
            }

            if (! Schema::hasColumn('work_outputs', 'title')) {
                $table->string('title')->nullable()->after('work_date');
            }

            if (! Schema::hasColumn('work_outputs', 'description')) {
                $table->text('description')->nullable()->after('title');
            }

            if (! Schema::hasColumn('work_outputs', 'accomplishments')) {
                $table->text('accomplishments')->nullable()->after('description');
            }

            if (! Schema::hasColumn('work_outputs', 'hours_worked')) {
                $table->decimal('hours_worked', 5, 2)->nullable()->after('accomplishments');
            }

            if (! Schema::hasColumn('work_outputs', 'status')) {
                $table->string('status')->default('submitted')->after('hours_worked');
            }

            if (! Schema::hasColumn('work_outputs', 'submitted_by')) {
                $table->foreignId('submitted_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('work_outputs', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->after('submitted_by')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('work_outputs', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }

            if (! Schema::hasColumn('work_outputs', 'review_remarks')) {
                $table->text('review_remarks')->nullable()->after('reviewed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('work_outputs', function (Blueprint $table) {
            foreach (['reviewed_by', 'submitted_by', 'job_listing_id', 'application_id'] as $column) {
                if (Schema::hasColumn('work_outputs', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }

            foreach ([
                'work_date',
                'title',
                'description',
                'accomplishments',
                'hours_worked',
                'status',
                'reviewed_at',
                'review_remarks',
            ] as $column) {
                if (Schema::hasColumn('work_outputs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
