<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Beneficiary;

class RoleAndAdminSeeder extends Seeder
{
    public function run()
    {
        // 1️⃣ Create Roles
        $roles = ['Admin', 'PESO', 'Employer', 'Beneficiary'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // 2️⃣ Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@spes.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password')
            ]
        );
        $admin->assignRole('Admin');

        // 3️⃣ Create PESO Users
        $peso1 = User::firstOrCreate(
            ['email' => 'peso1@spes.com'],
            [
                'name' => 'PESO Officer 1',
                'password' => bcrypt('secret123')
            ]
        );
        $peso1->assignRole('PESO');

        $peso2 = User::firstOrCreate(
            ['email' => 'peso2@spes.com'],
            [
                'name' => 'PESO Officer 2',
                'password' => bcrypt('secret123')
            ]
        );
        $peso2->assignRole('PESO');

        // 4️⃣ Create Employer User
        $employer = User::firstOrCreate(
            ['email' => 'employer1@spes.com'],
            [
                'name' => 'Employer 1',
                'password' => bcrypt('employer123')
            ]
        );
        $employer->assignRole('Employer');

        // 5️⃣ Create Beneficiary Users for ALL records in beneficiaries table
        $beneficiaries = Beneficiary::all();

        foreach ($beneficiaries as $b) {
            $user = User::firstOrCreate(
                ['email' => $b->email],
                [
                    'name' => $b->first_name . ' ' . $b->last_name,
                    'password' => bcrypt('beneficiary123'),
                ]
            );

            $user->assignRole('Beneficiary');

            // Link user_id to beneficiary
            $b->user_id = $user->id;
            $b->save();
        }
    }
}
