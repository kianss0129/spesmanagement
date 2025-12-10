<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EmployerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_name'   => $this->faker->company(),
            'contact_person' => $this->faker->name(),
            'email'          => $this->faker->unique()->safeEmail(),
            'phone'          => $this->faker->phoneNumber(),
            'address'        => $this->faker->address(),
        ];
    }
}