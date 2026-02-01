<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            if (!Schema::hasColumn('beneficiaries', 'approval_status')) {
                $table->string('approval_status')->default('pending'); // pending, approved, rejected
            }
        });
    }

    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            if (Schema::hasColumn('beneficiaries', 'approval_status')) {
                $table->dropColumn('approval_status');
            }
        });
    }
};
