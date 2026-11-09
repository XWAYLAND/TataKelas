<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;

class DemoAssignmentSeeder extends DemoFeatureSeeder
{
    public function run(): void
    {
        $this->ensureDemoEnvironment();

        DB::transaction(function (): void {
            $teacher = $this->demoUser(self::TEACHER_EMAIL);
            $classroom = $this->demoClassroom();

            $assignmentFolder = $this->demoFolder(
                $teacher,
                'Tugas: Rancang Halaman Profil',
                'Kumpulkan rancangan halaman profil sebelum tenggat.',
                $classroom,
                'assignment',
                now()->addDays(14)->startOfDay(),
            );

            $this->demoResource(
                $assignmentFolder,
                'Instruksi Tugas',
                'Buat halaman profil responsif, lalu kumpulkan tautan atau deskripsi hasil pekerjaan.',
                'assignment_instruction',
            );
        });
    }
}
