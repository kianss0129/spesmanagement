<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            // Drop existing FK
            $table->dropForeign('applications_job_listing_id_foreign');

            // Add cascade delete
            $table->foreign('job_listing_id')
                  ->references('id')
                  ->on('job_listings')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            // Remove cascade FK
            $table->dropForeign('applications_job_listing_id_foreign');

            // Restore original behavior (no cascade)
            $table->foreign('job_listing_id')
                  ->references('id')
                  ->on('job_listings');
        });
    }
};