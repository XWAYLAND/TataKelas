<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;

class DemoLearningMaterialSeeder extends DemoFeatureSeeder
{
    public function run(): void
    {
        $this->ensureDemoEnvironment();

        DB::transaction(function (): void {
            $teacher = $this->demoUser(self::TEACHER_EMAIL);
            $classroom = $this->demoClassroom();

            $materialFolder = $this->demoFolder(
                $teacher,
                'Materi: Pengantar Web',
                'Materi bersama yang dapat diakses anggota classroom.',
                $classroom,
                'class_material',
            );

            $this->demoResource($materialFolder, 'Dokumentasi HTML', 'https://developer.mozilla.org/en-US/docs/Web/HTML', 'documentation');
            $this->demoResource($materialFolder, 'Referensi CSS', 'https://developer.mozilla.org/en-US/docs/Web/CSS', 'url');
        });
    }
}
