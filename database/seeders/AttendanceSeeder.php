<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\Beneficiary;
use App\Models\Employer;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $beneficiaries = Beneficiary::all();
        $employers = Employer::all();

        foreach($beneficiaries as $b){
            foreach(range(1,10) as $i){ // 10 days
                Attendance::create([
                    'beneficiary_id'=>$b->id,
                    'employer_id'=>$employers->random()->id,
                    'date'=>now()->subDays($i),
                    'time_in'=>'08:00:00',
                    'time_out'=>'17:00:00',
                    'notes'=>'Sample attendance'
                ]);
            }
        }
    }
}
