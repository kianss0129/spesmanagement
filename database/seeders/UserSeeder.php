<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // ✅ Admin
        $this->createUser(
            name: 'SPES Admin',
            email: 'admin@spes.com',
            password: 'admin123',
            role: 'Admin'
        );

        // ✅ PESO Officers
        $this->createUser(
            name: 'PESO Officer 1',
            email: 'peso1@spes.com',
            password: 'peso123',
            role: 'PESO'
        );

        $this->createUser(
            name: 'PESO Officer 2',
            email: 'peso2@spes.com',
            password: 'peso123',
            role: 'PESO'
        );

        // ✅ Employers
        $this->createUser(
            name: 'Employer One',
            email: 'employer1@spes.com',
            password: 'employer123',
            role: 'Employer'
        );

        $this->createUser(
            name: 'Employer Two',
            email: 'employer2@spes.com',
            password: 'employer123',
            role: 'Employer'
        );

        // ✅ Beneficiaries
        $this->createUser(
            name: 'Juan Dela Cruz',
            email: 'juan@spes.com',
            password: 'juan123',
            role: 'Beneficiary'
        );

        $this->createUser(
            name: 'Maria Santos',
            email: 'maria@spes.com',
            password: 'maria123',
            role: 'Beneficiary'
        );

        $this->createUser(
            name: 'Pedro Reyes',
            email: 'pedro@spes.com',
            password: 'pedro123',
            role: 'Beneficiary'
        );

        $this->createUser(
            name: 'Ana Garcia',
            email: 'ana@spes.com',
            password: 'ana123',
            role: 'Beneficiary'
        );
    }

    /**
     * Create or update a user with the given role (column-based).
     */
    protected function createUser(string $name, string $email, string $password, string $role)
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role' => $role,
                'email_verified_at' => now(),
            ]
        );

        // If user exists but role is different, update it
        if ($user->role !== $role) {
            $user->role = $role;
            $user->save();
            echo "Updated role to {$role} for {$email}\n";
        } else {
            echo "User {$email} already has role {$role}\n";
        }
    }
}
