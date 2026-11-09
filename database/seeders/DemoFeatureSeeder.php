<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Folder;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use RuntimeException;

abstract class DemoFeatureSeeder extends Seeder
{
    protected const ADMIN_EMAIL = 'admin@tatakelas.test';

    protected const TEACHER_EMAIL = 'guru@tatakelas.test';

    protected const STUDENT_ONE_EMAIL = 'murid1@tatakelas.test';

    protected const STUDENT_TWO_EMAIL = 'murid2@tatakelas.test';

    protected const CLASSROOM_CODE = 'TATAKELAS';

    protected function ensureDemoEnvironment(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Demo accounts can only be seeded in local or testing environments.');
        }
    }

    protected function demoPassword(): string
    {
        $password = config('seeding.demo_password');

        if (! is_string($password) || strlen($password) < 12) {
            throw new RuntimeException('Set TATAKELAS_DEMO_PASSWORD to a password with at least 12 characters before seeding.');
        }

        return $password;
    }

    protected function demoUser(string $email): User
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            throw new RuntimeException("Demo user [{$email}] is missing. Run DemoUserSeeder first.");
        }

        return $user;
    }

    protected function demoClassroom(): Classroom
    {
        $classroom = Classroom::query()->where('code', self::CLASSROOM_CODE)->first();

        if (! $classroom) {
            throw new RuntimeException('Demo classroom is missing. Run DemoClassroomSeeder first.');
        }

        return $classroom;
    }

    protected function demoFolder(
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

    protected function demoResource(Folder $folder, string $title, string $url, string $type): Resource
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
