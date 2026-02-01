<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            if (!Schema::hasColumn('beneficiaries', 'skills')) {
                $table->string('skills')->nullable()->after('school_id');
            }
            if (!Schema::hasColumn('beneficiaries', 'parent_name')) {
                $table->string('parent_name')->nullable()->after('skills');
            }
        });
    }

    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            if (Schema::hasColumn('beneficiaries', 'skills')) {
                $table->dropColumn('skills');
            }
            if (Schema::hasColumn('beneficiaries', 'parent_name')) {
                $table->dropColumn('parent_name');
            }
        });
    }
};
