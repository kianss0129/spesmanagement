<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::table('employer_ratings', function (Blueprint $t) {
            // If a foreign key exists on employer_id, drop it
            try {
                $t->dropForeign(['employer_id']);
            } catch (\Throwable $e) {
                // ignore if foreign key does not exist
            }
        });

        Schema::table('employer_ratings', function (Blueprint $t) {
            // Ensure column exists
            if (! Schema::hasColumn('employer_ratings', 'employer_id')) {
                $t->unsignedBigInteger('employer_id')->after('id');
            }

            // Add foreign key to employers table
            try {
                $t->foreign('employer_id')->references('id')->on('employers')->onDelete('cascade');
            } catch (\Throwable $e) {
                // Fallback for sqlite or other DBs where constraints might be different
                DB::statement('PRAGMA foreign_keys = OFF');
            }
        });
    }

    public function down(): void {
        Schema::table('employer_ratings', function (Blueprint $t) {
            try {
                $t->dropForeign(['employer_id']);
            } catch (\Throwable $e) {
                // ignore
            }

            // Add back foreign key to users (original behavior) if needed
            try {
                $t->foreign('employer_id')->references('id')->on('users')->onDelete('cascade');
            } catch (\Throwable $e) {
                // ignore
            }
        });
    }
};