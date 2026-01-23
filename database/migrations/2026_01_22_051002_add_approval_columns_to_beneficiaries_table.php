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
        $table->unsignedBigInteger('approved_by')->nullable()->after('approved');
        $table->timestamp('approved_at')->nullable()->after('approved_by');
        $table->timestamp('rejected_at')->nullable()->after('approved_at');
    });
}

public function down()
{
    Schema::table('beneficiaries', function (Blueprint $table) {
        $table->dropColumn(['approved_by','approved_at','rejected_at']);
    });
}
};
