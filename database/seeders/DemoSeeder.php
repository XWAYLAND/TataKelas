<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->call([
                DemoUserSeeder::class,
                DemoClassroomSeeder::class,
                DemoPersonalFolderSeeder::class,
                DemoLearningMaterialSeeder::class,
                DemoAssignmentSeeder::class,
                DemoSubmissionSeeder::class,
            ]);
        });
    }
}
