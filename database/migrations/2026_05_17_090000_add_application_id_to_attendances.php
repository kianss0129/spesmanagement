<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->unsignedBigInteger('application_id')->nullable()->after('employer_id');
            $table->index('application_id');
            $table->foreign('application_id')->references('id')->on('applications')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['application_id']);
            $table->dropIndex(['application_id']);
            $table->dropColumn('application_id');
        });
    }
};
