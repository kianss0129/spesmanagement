<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up()
{
    Schema::table('beneficiaries', function (Blueprint $table) {
        $table->unsignedBigInteger('job_id')->nullable()->after('status');
        $table->unsignedBigInteger('employer_id')->nullable()->after('job_id');

        $table->foreign('job_id')->references('id')->on('job_listings')->onDelete('set null');
        $table->foreign('employer_id')->references('id')->on('employers')->onDelete('set null');
    });
}

public function down()
{
    Schema::table('beneficiaries', function (Blueprint $table) {
        $table->dropForeign(['job_id']);
        $table->dropForeign(['employer_id']);
        $table->dropColumn(['job_id', 'employer_id']);
    });
}
};
