<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the seeders in order
        $this->call([
            //RoleSeeder::class,
            RoleAndAdminSeeder::class,
            EmployerSeeder::class,
            // BeneficiarySeeder::class,
            ApplicationSeeder::class,

            AttendanceSeeder::class
        ]);
    }
}
