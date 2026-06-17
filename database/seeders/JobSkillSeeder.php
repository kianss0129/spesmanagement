<?php


namespace Database\Seeders;


use App\Models\JobListing;
use App\Models\Skill;
use Illuminate\Database\Seeder;


class JobSkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This seeder assigns skills to existing job listings for the matching feature to work
     */
    public function run(): void
    {
        // Get some jobs and skills
        $jobs = JobListing::limit(5)->get();
        $allSkills = Skill::all();


        if ($jobs->isEmpty() || $allSkills->isEmpty()) {
            $this->command->warn('No jobs or skills found. Please seed jobs and skills first.');
            return;
        }


        // Assign random skills to each job
        foreach ($jobs as $job) {
            // Get 2-4 random skills for this job
            $randomSkills = $allSkills->random(rand(2, 4));
            $skillIds = $randomSkills->pluck('id')->toArray();
           
            // Sync skills (this will create entries in job_listing_skills table)
            $job->skills()->sync($skillIds);
           
            $this->command->info("Assigned " . count($skillIds) . " skills to job: {$job->title}");
        }


        $this->command->info('Job-Skill relationships seeded successfully!');
    }
}



