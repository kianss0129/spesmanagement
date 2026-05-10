<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    if (!Schema::hasColumn('announcements', 'target_role')) {
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('target_role')->default('all')->after('image');
        });
    }
}

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn('target_role');
        });
    }
};