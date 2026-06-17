<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('skill_categories', function (Blueprint $table) {
            if (! Schema::hasColumn('skill_categories', 'name')) {
                $table->string('name')->nullable()->after('id');
            }

            if (! Schema::hasColumn('skill_categories', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('skill_categories', function (Blueprint $table) {
            if (Schema::hasColumn('skill_categories', 'description')) {
                $table->dropColumn('description');
            }

            if (Schema::hasColumn('skill_categories', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};
