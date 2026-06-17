<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {

            if (!Schema::hasColumn('beneficiaries', 'draft_status')) {
                $table->string('draft_status')
                      ->default('saved')
                      ->after('status');
            }

            if (!Schema::hasColumn('beneficiaries', 'completed_steps')) {
                $table->json('completed_steps')
                      ->nullable()
                      ->after('completion_percentage');
            }

        });
    }

    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {

            if (Schema::hasColumn('beneficiaries', 'draft_status')) {
                $table->dropColumn('draft_status');
            }

            if (Schema::hasColumn('beneficiaries', 'completed_steps')) {
                $table->dropColumn('completed_steps');
            }

        });
    }
};