<?php

namespace Tests\Feature;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_summary_and_recent_activity(): void
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
            'action' => AdminAuditLog::PASSWORD_RESET,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.index'))
            ->assertOk()
            ->assertSee('Dashboard admin')
            ->assertSee('Total akun')
            ->assertSee('Distribusi akun')
            ->assertSee('Aktivitas admin terbaru')
            ->assertSee('Pengguna terbaru')
            ->assertSee($student->name);
    }

    public function test_students_and_guests_cannot_open_the_admin_dashboard(): void
    {
        $student = User::factory()->create();

        $this->get(route('admin.index'))->assertRedirect(route('login'));

        $this->actingAs($student)
            ->get(route('admin.index'))
            ->assertForbidden();
    }

    private function createUserWithRole(string $role, ?string $email = null): User
    {
        $user = User::factory()->create($email === null ? [] : ['email' => $email]);
        $user->forceFill(['role' => $role])->save();

        return $user;
    }
}
