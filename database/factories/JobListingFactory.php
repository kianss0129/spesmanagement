<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class JobListingFactory extends Factory
{
    public function definition(): array
    {
       return [
    'title'       => $this->faker->jobTitle(),
    'description' => $this->faker->paragraph(),
    'employer_id' => null,                                     // set in seeder
    'positions'   => $this->faker->numberBetween(1, 5),
    'start_date'  => $this->faker->date(),
    'end_date'    => $this->faker->date(),
    'status'      => 'open',
];
    }
}
