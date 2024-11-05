<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{

    public function run()
    {
        $roles = [
            ['name' => 'Admin', 'reporting_user_id' => null],
            ['name' => 'Manager', 'reporting_user_id' => 1],
            ['name' => 'Team Lead', 'reporting_user_id' => 1],
            ['name' => 'Employee', 'reporting_user_id' => null, ],

        ];

        foreach ($roles as $roleData) {
            Role::create($roleData);
        }
    }
}
