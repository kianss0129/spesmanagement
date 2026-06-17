<?php

namespace Database\Seeders;

use App\Models\SkillCategory;
use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Information Technology (IT)',
                'description' => 'Technical and IT-related skills',
                'skills' => [
                    'IT Support',
                    'Computer Troubleshooting',
                    'Networking',
                    'Web Development',
                    'Software Development',
                    'Database Management',
                    'Technical Documentation',
                    'System Administration',
                    'Cybersecurity Basics',
                ],
            ],

            [
                'name' => 'Office Administration',
                'description' => 'Administrative and office-related skills',
                'skills' => [
                    'Data Encoding',
                    'MS Word',
                    'MS Excel',
                    'File Management',
                    'Record Keeping',
                ],
            ],

            [
                'name' => 'Customer Service',
                'description' => 'Customer-facing service skills',
                'skills' => [
                    'Customer Service',
                    'Communication',
                    'Problem Solving',
                    'Sales',
                ],
            ],

            [
                'name' => 'Construction',
                'description' => 'Construction and skilled trades',
                'skills' => [
                    'Carpentry',
                    'Masonry',
                    'Electrical Installation',
                    'Plumbing',
                    'Painting',
                ],
            ],

            [
                'name' => 'Engineering',
                'description' => 'Engineering and technical skills',
                'skills' => [
                    'Civil Engineering',
                    'Mechanical Engineering',
                    'Electrical Engineering',
                    'AutoCAD',
                ],
            ],

            [
                'name' => 'Healthcare',
                'description' => 'Healthcare and medical-related skills',
                'skills' => [
                    'Nursing Basics',
                    'First Aid',
                    'Patient Care',
                    'Medical Records',
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $category = SkillCategory::firstOrCreate(
                ['name' => $categoryData['name']],
                ['description' => $categoryData['description']]
            );

            foreach ($categoryData['skills'] as $skillName) {
                Skill::firstOrCreate(
                    ['name' => $skillName], 
                    ['skill_category_id' => $category->id]
                );
            }
        }
    }
}