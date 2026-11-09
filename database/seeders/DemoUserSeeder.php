<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class DemoUserSeeder extends DemoFeatureSeeder
{
    public function run(): void
    {
        $this->ensureDemoEnvironment();
        $password = $this->demoPassword();

        DB::transaction(function () use ($password): void {
            $this->user(self::ADMIN_EMAIL, 'Admin Demo', 'admin', $password);
            $this->user(self::TEACHER_EMAIL, 'Guru Demo', 'teacher', $password);
            $this->user(self::STUDENT_ONE_EMAIL, 'Murid Demo Satu', 'student', $password);
            $this->user(self::STUDENT_TWO_EMAIL, 'Murid Demo Dua', 'student', $password);
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
}
