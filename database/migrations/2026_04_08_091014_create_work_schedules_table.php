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
        if (! Schema::hasTable('work_schedules')) {
            Schema::create('work_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('beneficiary_id')->constrained()->cascadeOnDelete();
                $table->string('day')->nullable();
                $table->time('start_time');
                $table->time('end_time');
                $table->timestamps();
            });

            return;
        }

        Schema::table('work_schedules', function (Blueprint $table) {
            if (! Schema::hasColumn('work_schedules', 'day')) {
                $table->string('day')->nullable()->after('beneficiary_id');
            }
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('work_schedules') && Schema::hasColumn('work_schedules', 'day')) {
            Schema::table('work_schedules', function (Blueprint $table) {
                $table->dropColumn('day');
            });
        }
    }
};
