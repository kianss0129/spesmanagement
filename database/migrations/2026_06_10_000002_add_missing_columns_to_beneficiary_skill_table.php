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
        Schema::table('beneficiary_skill', function (Blueprint $table) {
            if (! Schema::hasColumn('beneficiary_skill', 'beneficiary_id')) {
                $table->foreignId('beneficiary_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('beneficiaries')
                    ->cascadeOnDelete();
            }

            if (! Schema::hasColumn('beneficiary_skill', 'skill_id')) {
                $table->foreignId('skill_id')
                    ->nullable()
                    ->after('beneficiary_id')
                    ->constrained('skills')
                    ->cascadeOnDelete();
            }
        });

        Schema::table('beneficiary_skill', function (Blueprint $table) {
            $table->unique(['beneficiary_id', 'skill_id'], 'beneficiary_skill_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beneficiary_skill', function (Blueprint $table) {
            $table->dropUnique('beneficiary_skill_unique');

            if (Schema::hasColumn('beneficiary_skill', 'skill_id')) {
                $table->dropConstrainedForeignId('skill_id');
            }

            if (Schema::hasColumn('beneficiary_skill', 'beneficiary_id')) {
                $table->dropConstrainedForeignId('beneficiary_id');
            }
        });
    }
};
