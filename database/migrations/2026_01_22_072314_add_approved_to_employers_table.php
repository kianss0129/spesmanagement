<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employers', function (Blueprint $table) {

            if (! Schema::hasColumn('employers', 'approved')) {
                $table->boolean('approved')->default(false);
            }

            if (! Schema::hasColumn('employers', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }

            if (! Schema::hasColumn('employers', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable();
            }

            if (! Schema::hasColumn('employers', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('employers', function (Blueprint $table) {

            if (Schema::hasColumn('employers', 'rejected_at')) {
                $table->dropColumn('rejected_at');
            }

            if (Schema::hasColumn('employers', 'approved_by')) {
                $table->dropColumn('approved_by');
            }

            if (Schema::hasColumn('employers', 'approved_at')) {
                $table->dropColumn('approved_at');
            }

            if (Schema::hasColumn('employers', 'approved')) {
                $table->dropColumn('approved');
            }
        });
    }
};
