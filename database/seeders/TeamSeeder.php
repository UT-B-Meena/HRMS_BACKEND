<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TeamSeeder extends Seeder
{

    public function run()
    {
        $teams = [
            ['name' => 'Admin', 'reporting_user_id' => 1, 'created_by' => 61,'updated_by' =>61],
            ['name' => 'Manager', 'reporting_user_id' => 1, 'created_by' => 61,'updated_by' =>61],
            ['name' => 'DevOps', 'reporting_user_id' => 3, 'created_by' => 61,'updated_by' =>61],
            ['name' => 'Backend', 'reporting_user_id' => 3, 'created_by' => 61,'updated_by' =>61],
            ['name' => 'Game Development', 'reporting_user_id' => 8, 'created_by' => 61,'updated_by' =>61],
            ['name' => 'Frontend', 'reporting_user_id' => 5, 'created_by' => 61,'updated_by' =>61],
            ['name' => 'UI/UX', 'reporting_user_id' => 5, 'created_by' => 61,'updated_by' =>61],
            ['name' => 'Game Artist', 'reporting_user_id' => 11, 'created_by' => 61,'updated_by' =>61],
            ['name' => 'Quality Analyst', 'reporting_user_id' => 2, 'created_by' => 61,'updated_by' =>61],
        ];

        foreach ($teams as $teamData) {
            Team::create($teamData);
        }
    }
}
