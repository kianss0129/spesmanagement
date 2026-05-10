<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['job_listing_id']);
        });

        DB::statement('ALTER TABLE applications MODIFY job_listing_id BIGINT UNSIGNED NULL;');

        Schema::table('applications', function (Blueprint $table) {
            $table->foreign('job_listing_id')
                  ->references('id')
                  ->on('job_listings')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['job_listing_id']);
        });

        DB::statement('ALTER TABLE applications MODIFY job_listing_id BIGINT UNSIGNED NOT NULL;');

        Schema::table('applications', function (Blueprint $table) {
            $table->foreign('job_listing_id')
                  ->references('id')
                  ->on('job_listings')
                  ->cascadeOnDelete();
        });
    }
};
