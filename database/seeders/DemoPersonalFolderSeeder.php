<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\Photo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DemoPersonalFolderSeeder extends DemoFeatureSeeder
{
    public function run(): void
    {
        $this->ensureDemoEnvironment();

        DB::transaction(function (): void {
            $teacher = $this->demoUser(self::TEACHER_EMAIL);
            $studentOne = $this->demoUser(self::STUDENT_ONE_EMAIL);
            $studentTwo = $this->demoUser(self::STUDENT_TWO_EMAIL);

            $teacherFolder = $this->demoFolder($teacher, 'Workspace Guru', 'Folder privat guru untuk referensi mengajar.');
            $studentFolder = $this->demoFolder($studentOne, 'Workspace Pribadi', 'Folder ini hanya dapat dilihat oleh pemiliknya.');
            $studentTwoFolder = $this->demoFolder($studentTwo, 'Catatan Belajar', 'Workspace pribadi murid kedua.');

            $this->demoResource($teacherFolder, 'Referensi Pengajaran', 'https://laravel.com/docs/12.x', 'documentation');
            $this->demoResource($studentFolder, 'Dokumentasi HTML', 'https://developer.mozilla.org/en-US/docs/Web/HTML', 'documentation');
            $this->demoResource($studentFolder, 'Video Belajar CSS', 'https://www.youtube.com/', 'video');
            $this->demoResource($studentTwoFolder, 'Belajar JavaScript', 'https://developer.mozilla.org/en-US/docs/Web/JavaScript', 'documentation');

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
        });
    }
}
