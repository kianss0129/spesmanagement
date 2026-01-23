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
        if (! Schema::hasColumn('beneficiaries', 'status')) {
            $table->string('status')->default('pending');
        }
    });
}

public function down()
{
    Schema::table('beneficiaries', function (Blueprint $table) {
        if (Schema::hasColumn('beneficiaries', 'status')) {
            $table->dropColumn('status');
        }
    });
}
};
