<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
    public function up(): void
    {
        Schema::table('employers', function (Blueprint $table) {
            if (! Schema::hasColumn('employers', 'details')) {
                $table->json('details')->nullable()->after('documents');
            }
        });
    }


    public function down(): void
    {
        Schema::table('employers', function (Blueprint $table) {
            if (Schema::hasColumn('employers', 'details')) {
                $table->dropColumn('details');
            }
        });
    }
};
