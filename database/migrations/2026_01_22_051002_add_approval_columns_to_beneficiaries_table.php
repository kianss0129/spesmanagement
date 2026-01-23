<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {

            // Add approved_by if missing
            if (! Schema::hasColumn('beneficiaries', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable();
            }

            // Add approved_at if missing
            if (! Schema::hasColumn('beneficiaries', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }

            // Add rejected_at if missing
            if (! Schema::hasColumn('beneficiaries', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {

            if (Schema::hasColumn('beneficiaries', 'rejected_at')) {
                $table->dropColumn('rejected_at');
            }

            if (Schema::hasColumn('beneficiaries', 'approved_at')) {
                $table->dropColumn('approved_at');
            }

            if (Schema::hasColumn('beneficiaries', 'approved_by')) {
                $table->dropColumn('approved_by');
            }
        });
    }
};
