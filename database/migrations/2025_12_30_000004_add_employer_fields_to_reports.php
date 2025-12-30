<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('reports', function (Blueprint $t) {
            // Add employer-oriented report fields (nullable)
            $t->unsignedBigInteger('employer_id')->nullable()->after('id');
            $t->string('title')->nullable()->after('employer_id');
            $t->text('body')->nullable()->after('title');
            $t->string('file_path')->nullable()->after('body');

            // Add foreign key
            $t->foreign('employer_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void {
        Schema::table('reports', function (Blueprint $t) {
            // Check if foreign key exists before dropping
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $doctrineTable = $sm->listTableDetails('reports');
            if ($doctrineTable->hasForeignKey('reports_employer_id_foreign')) {
                $t->dropForeign(['employer_id']);
            }
            $t->dropColumn(['employer_id','title','body','file_path']);
        });
    }
};
