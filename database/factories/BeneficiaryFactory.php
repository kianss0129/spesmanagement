<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Beneficiary;

class BeneficiaryFactory extends Factory
{
    protected $model = Beneficiary::class;

    public function definition()
    {
        return [
            'student_id' => $this->faker->unique()->numerify('S###'),
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'school_id' => null, // or random school id if you have a schools table
            'documents' => json_encode([]),
        ];
    }
}
