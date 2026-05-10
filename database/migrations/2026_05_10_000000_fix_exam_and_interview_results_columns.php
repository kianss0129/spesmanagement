<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('interviews')) {
            // Migrate any legacy values before changing the enum definition.
            DB::statement("UPDATE `interviews` SET `result` = 'failed' WHERE `result` = 'rejected'");
            DB::statement("ALTER TABLE `interviews` MODIFY `result` ENUM('pending','passed','failed') NOT NULL DEFAULT 'pending'");
        }

        if (Schema::hasTable('exams')) {
            Schema::table('exams', function (Blueprint $table) {
                if (!Schema::hasColumn('exams', 'status')) {
                    $table->string('status')->default('scheduled')->after('notes');
                }
                if (!Schema::hasColumn('exams', 'result')) {
                    $table->string('result')->nullable()->after('status');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('interviews')) {
            DB::statement("ALTER TABLE `interviews` MODIFY `result` ENUM('pending','passed','rejected') NOT NULL DEFAULT 'pending'");
        }

        if (Schema::hasTable('exams')) {
            Schema::table('exams', function (Blueprint $table) {
                if (Schema::hasColumn('exams', 'result')) {
                    $table->dropColumn('result');
                }
                if (Schema::hasColumn('exams', 'status')) {
                    $table->dropColumn('status');
                }
            });
        }
    }
};
