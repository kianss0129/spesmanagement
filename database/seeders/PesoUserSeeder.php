<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PesoUserSeeder extends Seeder
{
    public function run()
    {
        // Create 5 PESO users
        $pesoUsers = [
            [
                'name' => 'Maria Santos',
                'email' => 'maria.santos@peso.gov.ph',
                'password' => Hash::make('peso123'),
            ],
            [
                'name' => 'Juan dela Cruz',
                'email' => 'juan.delacruz@peso.gov.ph',
                'password' => Hash::make('peso123'),
            ],
            [
                'name' => 'Rosa Garcia',
                'email' => 'rosa.garcia@peso.gov.ph',
                'password' => Hash::make('peso123'),
            ],
            [
                'name' => 'Carlos Reyes',
                'email' => 'carlos.reyes@peso.gov.ph',
                'password' => Hash::make('peso123'),
            ],
            [
                'name' => 'Anna Mercado',
                'email' => 'anna.mercado@peso.gov.ph',
                'password' => Hash::make('peso123'),
            ],
        ];

        foreach ($pesoUsers as $pesoData) {
            $user = User::updateOrCreate(
                ['email' => $pesoData['email']],
                [
                    'name' => $pesoData['name'],
                    'password' => $pesoData['password'],
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles(['PESO']);
        }

        $this->command->info('5 PESO users seeded successfully!');
    }
}
