<?php

namespace Tests\Feature;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminExportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_export_streaming_csv_respects_filters(): void
    {
        $admin = $this->createUserWithRole('admin', 'bos@example.test');
        $teacher = $this->createUserWithRole('teacher', 'guru@example.test');
        $student = User::factory()->create(['email' => 'murid@example.test']);

        $content = str_replace("\r\n", '', $this->actingAs($admin)
            ->get(route('admin.users.export', ['role' => 'teacher']))
            ->assertOk()
            ->streamedContent());

        $this->assertStringContainsString('Nama,Email,Role,Status', $content);
        $this->assertStringContainsString('guru@example.test', $content);
        $this->assertStringNotContainsString($admin->password, $content);
        $this->assertStringNotContainsString($admin->email, $content);
        $this->assertStringNotContainsString($student->email, $content);
    }

    public function test_user_export_marks_users_without_login(): void
    {
        $admin = $this->createUserWithRole('admin');
        $content = str_replace("\r\n", '', $this->actingAs($admin)
            ->get(route('admin.users.export'))
            ->assertOk()
            ->streamedContent());

        $this->assertStringContainsString('Belum pernah login', $content);
    }

    public function test_non_admins_cannot_export_users(): void
    {
        $student = User::factory()->create();

        $this->get(route('admin.users.export'))->assertRedirect(route('login'));
        $this->actingAs($student)
            ->get(route('admin.users.export'))
            ->assertForbidden();
    }

    public function test_audit_log_export_contains_action_rows(): void
    {
        $admin = $this->createUserWithRole('admin');
        $student = User::factory()->create();

        AdminAuditLog::query()->create([
            'actor_user_id' => $admin->id,
            'actor_name' => $admin->name,
            'actor_email' => $admin->email,
            'target_user_id' => $student->id,
            'target_name' => $student->name,
            'target_email' => $student->email,
            'action' => AdminAuditLog::ACCOUNT_SUSPENDED,
            'details' => ['reason' => 'Tata tertib'],
        ]);

        $content = str_replace("\r\n", '', $this->actingAs($admin)
            ->get(route('admin.audit-logs.export'))
            ->assertOk()
            ->streamedContent());

        $this->assertStringContainsString('Waktu,Tindakan', $content);
        $this->assertStringContainsString('account_suspended', $content);
        $this->assertStringContainsString($student->email, $content);
    }

    private function createUserWithRole(string $role, ?string $email = null): User
    {
        $user = User::factory()->create($email === null ? [] : ['email' => $email]);
        $user->forceFill(['role' => $role])->save();

        return $user;
    }
}
