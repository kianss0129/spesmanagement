
<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    public function up(): void
    {
        // Convert invalid existing values first
        DB::table('beneficiaries')
            ->whereNull('employment_status')
            ->orWhereNotIn('employment_status', [
                'unassigned',
                'assigned',
                'employed',
                'unemployed',
                'completed'
            ])
            ->update([
                'employment_status' => 'unassigned'
            ]);


        DB::statement("
            ALTER TABLE beneficiaries
            MODIFY employment_status
            ENUM(
                'unassigned',
                'assigned',
                'employed',
                'unemployed',
                'completed'
            )
            NOT NULL
            DEFAULT 'unassigned'
        ");
    }


    public function down(): void
    {
        DB::table('beneficiaries')
            ->whereIn('employment_status', [
                'unassigned',
                'assigned',
                'completed'
            ])
            ->update([
                'employment_status' => 'unemployed'
            ]);


        DB::statement("
            ALTER TABLE beneficiaries
            MODIFY employment_status
            ENUM(
                'employed',
                'unemployed'
            )
            NOT NULL
            DEFAULT 'unemployed'
        ");
    }
};

