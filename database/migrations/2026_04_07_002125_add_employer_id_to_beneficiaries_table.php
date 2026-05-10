<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmployerIdToBeneficiariesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('beneficiaries', 'employer_id')) {
            Schema::table('beneficiaries', function (Blueprint $table) {
                $table->foreignId('employer_id')->nullable()->after('id'); // adjust position if needed
            });
        }
    }

    public function down()
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->dropColumn('employer_id');
        });
    }
}