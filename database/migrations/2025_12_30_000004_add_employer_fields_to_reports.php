<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {

            // employer_id
            if (! Schema::hasColumn('reports', 'employer_id')) {
                $table->unsignedBigInteger('employer_id')->nullable();
            }

            // employer_name
            if (! Schema::hasColumn('reports', 'employer_name')) {
                $table->string('employer_name')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {

            if (Schema::hasColumn('reports', 'employer_name')) {
                $table->dropColumn('employer_name');
            }
            if (Schema::hasColumn('reports', 'employer_id')) {
                $table->dropColumn('employer_id');
            }
        });
    }
};
