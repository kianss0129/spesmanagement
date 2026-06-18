<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('work_outputs', function (Blueprint $table) {
            if (! Schema::hasColumn('work_outputs', 'original_submitted_at')) {
                $table->timestamp('original_submitted_at')->nullable()->after('submitted_by');
            }

            if (! Schema::hasColumn('work_outputs', 'resubmitted_at')) {
                $table->timestamp('resubmitted_at')->nullable()->after('original_submitted_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('work_outputs', function (Blueprint $table) {
            foreach (['resubmitted_at', 'original_submitted_at'] as $column) {
                if (Schema::hasColumn('work_outputs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
