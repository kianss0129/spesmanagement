<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Beneficiary;
use Illuminate\Support\Facades\Hash;

class RoleAndAdminSeeder extends Seeder
{
    public function run()
    {
        // 1️⃣ Create Roles
        $roles = ['Admin', 'PESO', 'PESO Admin', 'Employer', 'Beneficiary'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // 2️⃣ Create Super Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@spes.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['PESO Admin']); // ensures only Admin role

        // 3️⃣ Create PESO Users
        $peso1 = User::updateOrCreate(
            ['email' => 'peso1@spes.com'],
            [
                'name' => 'PESO User 1',
                'password' => Hash::make('secret123'),
                'email_verified_at' => now(),
            ]
        );
        $peso1->syncRoles(['PESO Admin']); // ✅ must match middleware

        $peso2 = User::updateOrCreate(
            ['email' => 'peso2@spes.com'],
            [
                'name' => 'PESO User 2',
                'password' => Hash::make('secret123'),
                'email_verified_at' => now(),
            ]
        );
        $peso2->syncRoles(['PESO']); // ✅ must match middleware

        // 4️⃣ Create Employer User
        $employer = User::updateOrCreate(
            ['email' => 'employer1@spes.com'],
            [
                'name' => 'Employer 1',
                'password' => Hash::make('employer123'),
                'email_verified_at' => now(),
            ]
        );
        $employer->syncRoles(['Employer']);

        // 5️⃣ Create Beneficiary Users for all records in beneficiaries table
        $beneficiaries = Beneficiary::all();

        foreach ($beneficiaries as $b) {
            $user = User::updateOrCreate(
                ['email' => $b->email],
                [
                    'name' => $b->first_name . ' ' . $b->last_name,
                    'password' => Hash::make('beneficiary123'),
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles(['Beneficiary']);

            // Link user_id to beneficiary
            $b->user_id = $user->id;
            $b->save();
        }

        $this->command->info('Roles and users seeded successfully!');

    }
}
