<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleAndAdminSeeder extends Seeder
{
    public function run()
    {
        // Roles
        $roles = ['Admin','PESO','Employer','Beneficiary'];
        foreach($roles as $role){
            Role::firstOrCreate(['name'=>$role]);
        }

        // Admin user
        $admin = User::firstOrCreate(
            ['email'=>'admin@spes.com'],
            ['name'=>'Super Admin','password'=>bcrypt('password')]
        );
        $admin->assignRole('Admin');
    }
}
