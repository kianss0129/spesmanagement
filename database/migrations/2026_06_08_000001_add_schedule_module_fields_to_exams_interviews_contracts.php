<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addCommonScheduleColumns('exams');
        $this->addCommonScheduleColumns('interviews');
        $this->addCommonScheduleColumns('contracts');

        if (Schema::hasTable('interviews') && ! Schema::hasColumn('interviews', 'interviewer_id')) {
            Schema::table('interviews', function (Blueprint $table) {
                $table->foreignId('interviewer_id')
                    ->nullable()
                    ->after('scheduled_by')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('interviews') && Schema::hasColumn('interviews', 'interviewer_id')) {
            Schema::table('interviews', function (Blueprint $table) {
                $table->dropForeign(['interviewer_id']);
                $table->dropColumn('interviewer_id');
            });
        }

        $this->dropCommonScheduleColumns('contracts');
        $this->dropCommonScheduleColumns('interviews');
        $this->dropCommonScheduleColumns('exams');
    }

    private function addCommonScheduleColumns(string $tableName): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (! Schema::hasColumn($tableName, 'schedule_group_id')) {
                $table->string('schedule_group_id')->nullable()->index();
            }

            if (! Schema::hasColumn($tableName, 'batch_title')) {
                $table->string('batch_title')->nullable();
            }

            if (! Schema::hasColumn($tableName, 'scheduled_by')) {
                $table->foreignId('scheduled_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn($tableName, 'end_at')) {
                $table->dateTime('end_at')->nullable();
            }

            if (! Schema::hasColumn($tableName, 'original_schedule_at')) {
                $table->dateTime('original_schedule_at')->nullable();
            }

            if (! Schema::hasColumn($tableName, 'rescheduled_at')) {
                $table->dateTime('rescheduled_at')->nullable();
            }

            if (! Schema::hasColumn($tableName, 'reschedule_reason')) {
                $table->text('reschedule_reason')->nullable();
            }

            if (! Schema::hasColumn($tableName, 'instructions')) {
                $table->text('instructions')->nullable();
            }

            if (! Schema::hasColumn($tableName, 'notify_beneficiaries')) {
                $table->boolean('notify_beneficiaries')->default(true);
            }
        });
    }

    private function dropCommonScheduleColumns(string $tableName): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (Schema::hasColumn($tableName, 'scheduled_by')) {
                $table->dropForeign(['scheduled_by']);
            }

            $columns = [
                'schedule_group_id',
                'batch_title',
                'scheduled_by',
                'end_at',
                'original_schedule_at',
                'rescheduled_at',
                'reschedule_reason',
                'instructions',
                'notify_beneficiaries',
            ];

            $existingColumns = array_filter(
                $columns,
                fn (string $column): bool => Schema::hasColumn($tableName, $column)
            );

            if ($existingColumns !== []) {
                $table->dropColumn($existingColumns);
            }
        });
    }
};
