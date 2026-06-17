<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('applications')) {
            Schema::table('applications', function (Blueprint $table) {
                if (! Schema::hasColumn('applications', 'employer_acknowledged_at')) {
                    $table->timestamp('employer_acknowledged_at')->nullable();
                }

                if (! Schema::hasColumn('applications', 'employer_acknowledged_by')) {
                    $table->foreignId('employer_acknowledged_by')
                        ->nullable()
                        ->constrained('users')
                        ->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                if (! Schema::hasColumn('attendances', 'review_status')) {
                    $table->string('review_status')->nullable();
                }

                if (! Schema::hasColumn('attendances', 'review_remarks')) {
                    $table->text('review_remarks')->nullable();
                }

                if (! Schema::hasColumn('attendances', 'reviewed_by')) {
                    $table->foreignId('reviewed_by')
                        ->nullable()
                        ->constrained('users')
                        ->nullOnDelete();
                }

                if (! Schema::hasColumn('attendances', 'reviewed_at')) {
                    $table->timestamp('reviewed_at')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                if (Schema::hasColumn('attendances', 'reviewed_by')) {
                    $table->dropForeign(['reviewed_by']);
                }

                $columns = array_values(array_filter([
                    Schema::hasColumn('attendances', 'reviewed_at') ? 'reviewed_at' : null,
                    Schema::hasColumn('attendances', 'reviewed_by') ? 'reviewed_by' : null,
                    Schema::hasColumn('attendances', 'review_remarks') ? 'review_remarks' : null,
                    Schema::hasColumn('attendances', 'review_status') ? 'review_status' : null,
                ]));

                if ($columns) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasTable('applications')) {
            Schema::table('applications', function (Blueprint $table) {
                if (Schema::hasColumn('applications', 'employer_acknowledged_by')) {
                    $table->dropForeign(['employer_acknowledged_by']);
                }

                $columns = array_values(array_filter([
                    Schema::hasColumn('applications', 'employer_acknowledged_by') ? 'employer_acknowledged_by' : null,
                    Schema::hasColumn('applications', 'employer_acknowledged_at') ? 'employer_acknowledged_at' : null,
                ]));

                if ($columns) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
