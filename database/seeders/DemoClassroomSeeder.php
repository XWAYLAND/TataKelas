<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;

class DemoClassroomSeeder extends DemoFeatureSeeder
{
    public function run(): void
    {
        $this->ensureDemoEnvironment();

        DB::transaction(function (): void {
            $teacher = $this->demoUser(self::TEACHER_EMAIL);
            $studentOne = $this->demoUser(self::STUDENT_ONE_EMAIL);
            $studentTwo = $this->demoUser(self::STUDENT_TWO_EMAIL);

            $classroom = $teacher->teachingClassrooms()->updateOrCreate(
                ['code' => self::CLASSROOM_CODE],
                [
                    'name' => 'Kelas Demo TataKelas',
                    'description' => 'Classroom contoh untuk mencoba materi, tugas, pengumpulan, dan penilaian.',
                ],
            );
            $classroom->students()->syncWithoutDetaching([$studentOne->id, $studentTwo->id]);
        });
    }
}
