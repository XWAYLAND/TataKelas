<?php

namespace Tests\Feature;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_detail_page_is_only_for_admins(): void
    {
        $admin = $this->createUserWithRole('admin');
        $student = User::factory()->create();

        $this->get(route('admin.users.show', $student))->assertRedirect(route('login'));
        $this->actingAs($student)->get(route('admin.users.show', $student))->assertForbidden();

        $this->actingAs($admin)
            ->get(route('admin.users.show', $student))
            ->assertOk()
            ->assertSee($student->name)
            ->assertSee($student->email);
    }

    public function test_detail_page_lists_related_classrooms_and_counts(): void
    {
        $admin = $this->createUserWithRole('admin');
        $teacher = $this->createUserWithRole('teacher', 'guru@example.test');
        $student = User::factory()->create(['email' => 'murid@example.test']);

        $classroom = $teacher->teachingClassrooms()->create([
            'name' => 'Kimia Dasar',
            'code' => 'KIM-101',
        ]);
        $classroom->students()->attach($student);
        $coTaught = User::factory()->create();
        $coTaught->forceFill(['role' => 'teacher'])->save();
        $coClassroom = $coTaught->teachingClassrooms()->create([
            'name' => 'Kimia Lanjut',
            'code' => 'KIM-201',
        ]);
        $coClassroom->coTeachers()->attach($teacher);

        $student->folders()->create(['name' => 'Catatan Kimia']);

        $response = $this->actingAs($admin)
            ->get(route('admin.users.show', $teacher))
            ->assertOk();

        $response->assertSee('Kimia Dasar')
            ->assertSee('Kimia Lanjut')
            ->assertSee('Co-teaching')
            ->assertSee('Data akun')
            ->assertSee('Folders');
    }

    public function test_detail_page_shows_suspension_context_and_audit_history(): void
    {
        $admin = $this->createUserWithRole('admin');
        $student = User::factory()->create();
        $student->forceFill([
            'status' => User::STATUS_SUSPENDED,
            'suspension_reason' => 'Pelanggaran tata tertib',
        ])->save();

        AdminAuditLog::query()->create([
            'actor_user_id' => $admin->id,
            'actor_name' => $admin->name,
            'actor_email' => $admin->email,
            'target_user_id' => $student->id,
            'target_name' => $student->name,
            'target_email' => $student->email,
            'action' => AdminAuditLog::ACCOUNT_SUSPENDED,
            'details' => ['reason' => 'Pelanggaran tata tertib'],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.show', $student))
            ->assertOk()
            ->assertSee('Ditangguhkan')
            ->assertSee('Pelanggaran tata tertib')
            ->assertSee('Aktifkan kembali')
            ->assertSee('Riwayat audit akun ini');
    }

    public function test_last_login_is_recorded_after_login(): void
    {
        $student = User::factory()->create([
            'email' => 'murid@example.test',
            'password' => 'password123',
        ]);

        $this->assertNull($student->fresh()->last_login_at);

        $this->post(route('login'), [
            'email' => 'murid@example.test',
            'password' => 'password123',
        ])->assertRedirect();

        $this->assertNotNull($student->fresh()->last_login_at);
    }

    private function createUserWithRole(string $role, ?string $email = null): User
    {
        $user = User::factory()->create($email === null ? [] : ['email' => $email]);
        $user->forceFill(['role' => $role])->save();

        return $user;
    }
}
