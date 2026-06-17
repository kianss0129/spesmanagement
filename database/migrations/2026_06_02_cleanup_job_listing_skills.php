<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the table if it exists (from the failed migration)
        DB::statement('DROP TABLE IF EXISTS job_listing_skills');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to rollback
    }
};
