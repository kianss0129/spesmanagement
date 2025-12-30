<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('attendances', function (Blueprint $t) {
            if (! Schema::hasColumn('attendances', 'status')) {
                $t->string('status')->nullable()->after('date');
            }
            if (! Schema::hasColumn('attendances', 'remarks')) {
                $t->text('remarks')->nullable()->after('status');
            }
        });
    }

    public function down(): void {
        Schema::table('attendances', function (Blueprint $t) {
            if (Schema::hasColumn('attendances', 'remarks')) {
                $t->dropColumn('remarks');
            }
            if (Schema::hasColumn('attendances', 'status')) {
                $t->dropColumn('status');
            }
        });
    }
};