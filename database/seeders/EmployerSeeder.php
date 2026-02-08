<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employer;
use Illuminate\Database\Seeder;

class EmployerSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'role' => 'employer', // adjust if you use roles
        ]);

        Employer::create([
            'user_id' => $user->id,
            'company_name' => fake()->company(),
            'address' => fake()->address(),
            'contact_person' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
        ]);
    }
}
