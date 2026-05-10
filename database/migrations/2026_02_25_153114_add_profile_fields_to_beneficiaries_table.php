<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->date('birthdate')->nullable()->after('phone');
            $table->string('gender')->nullable()->after('birthdate');
            $table->string('address')->nullable()->after('gender');
            $table->string('program')->nullable()->after('school_id');
            $table->string('year_level')->nullable()->after('program');
        });
    }

    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->dropColumn([
                'birthdate',
                'gender',
                'address',
                'program',
                'year_level',
            ]);
        });
    }
};