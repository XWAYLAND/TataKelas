<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Folder;
use App\Models\Note;
use App\Models\Photo;
use App\Models\Resource;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class TataKelasDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Demo accounts can only be seeded in local or testing environments.');
        }

        $password = config('seeding.demo_password');

        if (! is_string($password) || strlen($password) < 12) {
            throw new RuntimeException('Set TATAKELAS_DEMO_PASSWORD to a password with at least 12 characters before seeding.');
        }

        DB::transaction(function () use ($password): void {
            $admin = $this->user('admin@tatakelas.test', 'Admin Demo', 'admin', $password);
            $teacher = $this->user('guru@tatakelas.test', 'Guru Demo', 'teacher', $password);
            $studentOne = $this->user('murid1@tatakelas.test', 'Murid Demo Satu', 'student', $password);
            $studentTwo = $this->user('murid2@tatakelas.test', 'Murid Demo Dua', 'student', $password);

            $classroom = $teacher->teachingClassrooms()->updateOrCreate(
                ['code' => 'TATAKELAS'],
                [
                    'name' => 'Kelas Demo TataKelas',
                    'description' => 'Classroom contoh untuk mencoba materi, tugas, pengumpulan, dan penilaian.',
                ],
            );
            $classroom->students()->syncWithoutDetaching([$studentOne->id, $studentTwo->id]);

            $teacherFolder = $this->folder($teacher, 'Workspace Guru', 'Folder privat guru untuk referensi mengajar.');
            $studentFolder = $this->folder($studentOne, 'Workspace Pribadi', 'Folder ini hanya dapat dilihat oleh pemiliknya.');
            $studentTwoFolder = $this->folder($studentTwo, 'Catatan Belajar', 'Workspace pribadi murid kedua.');
            $materialFolder = $this->folder(
                $teacher,
                'Materi: Pengantar Web',
                'Materi bersama yang dapat diakses anggota classroom.',
                $classroom,
                'class_material',
            );
            $assignmentFolder = $this->folder(
                $teacher,
                'Tugas: Rancang Halaman Profil',
                'Kumpulkan rancangan halaman profil sebelum tenggat.',
                $classroom,
                'assignment',
                now()->addDays(14)->startOfDay(),
            );

            $this->resource($teacherFolder, 'Referensi Pengajaran', 'https://laravel.com/docs/12.x', 'documentation');
            $this->resource($studentFolder, 'Dokumentasi HTML', 'https://developer.mozilla.org/en-US/docs/Web/HTML', 'documentation');
            $this->resource($studentFolder, 'Video Belajar CSS', 'https://www.youtube.com/', 'video');
            $this->resource($studentTwoFolder, 'Belajar JavaScript', 'https://developer.mozilla.org/en-US/docs/Web/JavaScript', 'documentation');
            $this->resource($materialFolder, 'Dokumentasi HTML', 'https://developer.mozilla.org/en-US/docs/Web/HTML', 'documentation');
            $this->resource($materialFolder, 'Referensi CSS', 'https://developer.mozilla.org/en-US/docs/Web/CSS', 'url');
            $this->resource(
                $assignmentFolder,
                'Instruksi Tugas',
                'Buat halaman profil responsif, lalu kumpulkan tautan atau deskripsi hasil pekerjaan.',
                'assignment_instruction',
            );

            Note::query()->updateOrCreate(
                ['folder_id' => $studentFolder->id, 'title' => 'Target Belajar Minggu Ini'],
                [
                    'content' => 'Pelajari struktur HTML, latihan CSS responsif, dan simpan referensi penting di workspace.',
                    'priority' => 'high',
                ],
            );

            Note::query()->updateOrCreate(
                ['folder_id' => $teacherFolder->id, 'title' => 'Rencana Pertemuan'],
                [
                    'content' => 'Mulai dengan materi HTML, lanjutkan diskusi CSS, lalu berikan tugas halaman profil.',
                    'priority' => 'medium',
                ],
            );

            $photoPath = 'photos/demo-seed/study.gif';
            $photoContents = base64_decode('R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=', true);

            if ($photoContents === false || ! Storage::disk('local')->put($photoPath, $photoContents)) {
                throw new RuntimeException('Unable to write the sample private photo.');
            }

            Photo::query()->updateOrCreate(
                ['folder_id' => $studentFolder->id, 'title' => 'Contoh Foto Workspace'],
                [
                    'filename' => 'study.gif',
                    'filepath' => $photoPath,
                    'mime_type' => 'image/gif',
                    'file_size' => strlen($photoContents),
                    'description' => 'Contoh foto privat untuk mencoba galeri workspace.',
                ],
            );

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

    private function user(string $email, string $name, string $role, string $password): User
    {
        $user = User::query()->firstOrNew(['email' => $email]);
        $user->forceFill([
            'name' => $name,
            'password' => $password,
            'role' => $role,
            'email_verified_at' => now(),
        ])->save();

        return $user;
    }

    private function folder(
        User $owner,
        string $name,
        string $description,
        ?Classroom $classroom = null,
        string $visibility = 'private',
        ?Carbon $dueDate = null,
    ): Folder {
        $folder = Folder::withoutGlobalScopes()->firstOrNew([
            'user_id' => $owner->id,
            'classroom_id' => $classroom?->id,
            'name' => $name,
        ]);
        $folder->forceFill([
            'description' => $description,
            'color_code' => $visibility === 'private' ? '#3B82F6' : '#7C3AED',
            'visibility' => $visibility,
            'due_date' => $dueDate,
        ])->save();

        return $folder;
    }

    private function resource(Folder $folder, string $title, string $url, string $type): Resource
    {
        return Resource::query()->updateOrCreate(
            ['folder_id' => $folder->id, 'title' => $title],
            [
                'url' => $url,
                'resource_type' => $type,
                'notes' => 'Data contoh untuk mencoba fitur resource.',
                'read_status' => 'to_read',
            ],
        );
    }
}
