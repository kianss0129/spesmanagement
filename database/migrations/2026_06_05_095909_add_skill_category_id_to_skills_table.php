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
        Schema::table('skills', function (Blueprint $table) {
            $table->unsignedBigInteger('skill_category_id')->nullable()->after('name');
            $table->foreign('skill_category_id')
                ->references('id')
                ->on('skill_categories')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->dropForeign(['skill_category_id']);
            $table->dropColumn('skill_category_id');
        });
    }
};
