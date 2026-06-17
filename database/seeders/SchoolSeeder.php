<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('schools')->insert([
            ['name' => 'Lagro High School'],
            ['name' => 'Novaliches High School'],
            ['name' => 'Bestlink College'],
            ['name' => 'Quezon City University'],
            ['name' => 'STI College Novaliches'],
            ['name' => 'Our Lady of Fatima University'],
            ['name' => 'Metro Manila College'],
            ['name' => 'AMA Computer College'],
        ]);
    }
}