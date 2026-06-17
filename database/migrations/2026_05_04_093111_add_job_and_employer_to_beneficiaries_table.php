<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            if (! Schema::hasColumn('beneficiaries', 'job_id')) {
                $table->unsignedBigInteger('job_id')->nullable();
            }

            if (! Schema::hasColumn('beneficiaries', 'employer_id')) {
                $table->unsignedBigInteger('employer_id')->nullable();
            }
        });

        Schema::table('beneficiaries', function (Blueprint $table) {
            if (! $this->foreignKeyExists('beneficiaries', 'beneficiaries_job_id_foreign')) {
                $table->foreign('job_id')
                    ->references('id')
                    ->on('job_listings')
                    ->onDelete('set null');
            }

            if (! $this->foreignKeyExists('beneficiaries', 'beneficiaries_employer_id_foreign')) {
                $table->foreign('employer_id')
                    ->references('id')
                    ->on('employers')
                    ->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            if ($this->foreignKeyExists('beneficiaries', 'beneficiaries_job_id_foreign')) {
                $table->dropForeign(['job_id']);
            }

            if ($this->foreignKeyExists('beneficiaries', 'beneficiaries_employer_id_foreign')) {
                $table->dropForeign(['employer_id']);
            }
        });

        Schema::table('beneficiaries', function (Blueprint $table) {
            if (Schema::hasColumn('beneficiaries', 'job_id')) {
                $table->dropColumn('job_id');
            }

            if (Schema::hasColumn('beneficiaries', 'employer_id')) {
                $table->dropColumn('employer_id');
            }
        });
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        $result = DB::selectOne(
            'SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
                AND CONSTRAINT_NAME = ?
                AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$table, $constraint]
        );

        return $result !== null;
    }
};
