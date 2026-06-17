<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('interviews')) {
            return;
        }

        Schema::table('interviews', function (Blueprint $table) {
            if (! Schema::hasColumn('interviews', 'remarks')) {
                $table->text('remarks')->nullable()->after('result');
            }

            if (! Schema::hasColumn('interviews', 'evaluated_at')) {
                $table->timestamp('evaluated_at')->nullable()->after('remarks');
            }
        });

        DB::statement("ALTER TABLE interviews MODIFY result ENUM('pending', 'passed', 'failed', 'needs_review') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        if (! Schema::hasTable('interviews')) {
            return;
        }

        DB::table('interviews')
            ->where('result', 'needs_review')
            ->update(['result' => 'pending']);

        DB::statement("ALTER TABLE interviews MODIFY result ENUM('pending', 'passed', 'failed') NOT NULL DEFAULT 'pending'");

        Schema::table('interviews', function (Blueprint $table) {
            if (Schema::hasColumn('interviews', 'evaluated_at')) {
                $table->dropColumn('evaluated_at');
            }

            if (Schema::hasColumn('interviews', 'remarks')) {
                $table->dropColumn('remarks');
            }
        });
    }
};
