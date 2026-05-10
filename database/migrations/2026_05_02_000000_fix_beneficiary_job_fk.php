<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->dropForeign(['job_id']);
        });

        DB::table('beneficiaries')
            ->whereNotIn('job_id', function ($query) {
                $query->select('id')->from('job_listings');
            })
            ->update(['job_id' => null]);

        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->foreign('job_id')->references('id')->on('job_listings')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->dropForeign(['job_id']);
            $table->foreign('job_id')->references('id')->on('jobs')->onDelete('set null');
        });
    }
};
