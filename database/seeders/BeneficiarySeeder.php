<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Beneficiary;

class BeneficiarySeeder extends Seeder
{
    public function run()
    {
        Beneficiary::factory(20)->create();
    }
}
