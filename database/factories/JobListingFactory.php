<?php

namespace Database\Factories;

use App\Models\JobListing;
use App\Models\Employer;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobListingFactory extends Factory
{
    protected $model = JobListing::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->jobTitle,
            'description' => $this->faker->paragraph,
            'location' => $this->faker->city,
            'salary' => $this->faker->numberBetween(10000, 50000),

            // ✅ Automatically create a valid employer
            'employer_id' => Employer::factory(),
        ];
    }
}
