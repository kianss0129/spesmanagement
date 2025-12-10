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
    Schema::table('job_listings', function (Blueprint $table) {
        $table->foreignId('assigned_beneficiary_id')
              ->nullable()
              ->constrained('beneficiaries')
              ->nullOnDelete();

        $table->boolean('employer_choice')->default(false);
    });
}

public function down()
{
    Schema::table('job_listings', function (Blueprint $table) {
        $table->dropColumn(['assigned_beneficiary_id', 'employer_choice']);
    });
}

};
