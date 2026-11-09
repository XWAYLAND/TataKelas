<?php

namespace Database\Seeders;

use App\Models\Folder;
use App\Models\Submission;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DemoSubmissionSeeder extends DemoFeatureSeeder
{
    public function run(): void
    {
        $this->ensureDemoEnvironment();

        DB::transaction(function (): void {
            $teacher = $this->demoUser(self::TEACHER_EMAIL);
            $studentOne = $this->demoUser(self::STUDENT_ONE_EMAIL);
            $studentTwo = $this->demoUser(self::STUDENT_TWO_EMAIL);
            $classroom = $this->demoClassroom();

            $assignmentFolder = Folder::withoutGlobalScopes()
                ->where('user_id', $teacher->id)
                ->where('classroom_id', $classroom->id)
                ->where('name', 'Tugas: Rancang Halaman Profil')
                ->first();

            if (! $assignmentFolder) {
                throw new RuntimeException('Demo assignment folder is missing. Run DemoAssignmentSeeder first.');
            }

            Submission::query()->updateOrCreate(
                ['folder_id' => $assignmentFolder->id, 'student_id' => $studentOne->id],
                [
                    'submission_text' => 'Saya sudah membuat rancangan halaman profil responsif.',
                    'submitted_url' => 'https://example.com/portfolio',
                    'grade' => 92,
                    'teacher_feedback' => 'Struktur dan responsivitasnya sudah baik.',
                    'status' => 'graded',
                ],
            );

            Submission::query()->updateOrCreate(
                ['folder_id' => $assignmentFolder->id, 'student_id' => $studentTwo->id],
                [
                    'submission_text' => 'Berikut rancangan awal halaman profil saya.',
                    'submitted_url' => 'https://example.com/student-profile',
                    'grade' => null,
                    'teacher_feedback' => null,
                    'status' => 'submitted',
                ],
            );
        });
    }
}
