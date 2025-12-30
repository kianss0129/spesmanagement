<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interviews', function (Blueprint $t) {
            if (! Schema::hasColumn('interviews', 'application_id')) {
                $t->unsignedBigInteger('application_id')->nullable()->after('id');
                // No foreign key to keep migration simple for tests
            }
        });
    }

    public function down(): void
    {
        Schema::table('interviews', function (Blueprint $t) {
            if (Schema::hasColumn('interviews', 'application_id')) {
                $t->dropColumn('application_id');
            }
        });
    }
};
