<?php

namespace Tests\Feature;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_suspend_and_reactivate_a_student_account(): void
    {
        $admin = $this->createUserWithRole('admin');
        $student = User::factory()->create();
        $student->forceFill(['remember_token' => 'original-token'])->save();

        $this->actingAs($admin)
            ->patch(route('admin.users.suspend', $student), [
                'reason' => 'Pelanggaran tata tertib',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $student->refresh();

        $this->assertTrue($student->isSuspended());
        $this->assertSame('Pelanggaran tata tertib', $student->suspension_reason);
        $this->assertNotNull($student->suspended_at);
        $this->assertNotSame('original-token', $student->remember_token);
        $this->assertDatabaseHas('admin_audit_logs', [
            'actor_user_id' => $admin->id,
            'target_user_id' => $student->id,
            'action' => AdminAuditLog::ACCOUNT_SUSPENDED,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.users.reactivate', $student))
            ->assertRedirect()
            ->assertSessionHas('success');

        $student->refresh();

        $this->assertTrue($student->isActive());
        $this->assertNull($student->suspension_reason);
        $this->assertDatabaseHas('admin_audit_logs', [
            'actor_user_id' => $admin->id,
            'target_user_id' => $student->id,
            'action' => AdminAuditLog::ACCOUNT_REACTIVATED,
        ]);
    }

    public function test_suspended_account_cannot_log_back_in(): void
    {
        $this->createUserWithRole('admin');
        $student = User::factory()->create([
            'email' => 'murid@example.test',
            'password' => 'password123',
        ]);
        $student->forceFill(['status' => User::STATUS_SUSPENDED])->save();

        $this->post(route('login'), [
            'email' => 'murid@example.test',
            'password' => 'password123',
        ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_suspended_session_is_ended_on_next_request(): void
    {
        $student = User::factory()->create();
        $student->forceFill(['status' => User::STATUS_SUSPENDED])->save();

        $this->actingAs($student->fresh())
            ->get(route('folders.index'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_admin_cannot_suspend_own_account(): void
    {
        $admin = $this->createUserWithRole('admin');

        $this->actingAs($admin)
            ->patch(route('admin.users.suspend', $admin), ['reason' => 'Test'])
            ->assertSessionHasErrors('account');

        $this->assertSame(User::STATUS_ACTIVE, $admin->fresh()->status);
        $this->assertDatabaseMissing('admin_audit_logs', ['action' => AdminAuditLog::ACCOUNT_SUSPENDED]);
    }

    public function test_reactivating_an_active_account_returns_an_error(): void
    {
        $admin = $this->createUserWithRole('admin');
        $student = User::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.users.reactivate', $student))
            ->assertSessionHasErrors('account');

        $this->assertSame(User::STATUS_ACTIVE, $student->fresh()->status);
    }

    public function test_bulk_suspension_reports_processed_and_skipped_accounts(): void
    {
        $admin = $this->createUserWithRole('admin');
        $first = User::factory()->create();
        $second = User::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.users.bulk'), [
                'action' => 'suspend',
                'ids' => [$first->id, $second->id, $admin->id],
                'reason' => 'Uji massal',
            ])
            ->assertRedirect()
            ->assertSessionHas('bulk_result');

        $second->refresh();

        $this->assertTrue($second->isSuspended());
        $this->assertSame(User::STATUS_ACTIVE, $admin->fresh()->status);

        $this->assertDatabaseHas('admin_audit_logs', [
            'target_user_id' => $second->id,
            'action' => AdminAuditLog::ACCOUNT_SUSPENDED,
        ]);
    }

    public function test_bulk_role_change_updates_the_selected_role(): void
    {
        $admin = $this->createUserWithRole('admin');
        $student = User::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.users.bulk'), [
                'action' => 'role',
                'role' => 'teacher',
                'ids' => [$student->id],
            ])
            ->assertRedirect();

        $this->assertSame('teacher', $student->fresh()->role);
        $this->assertDatabaseHas('admin_audit_logs', [
            'actor_user_id' => $admin->id,
            'target_user_id' => $student->id,
            'action' => AdminAuditLog::ROLE_CHANGED,
        ]);
    }

    private function createUserWithRole(string $role, ?string $email = null): User
    {
        $user = User::factory()->create($email === null ? [] : ['email' => $email]);
        $user->forceFill(['role' => $role])->save();

        return $user;
    }
}
