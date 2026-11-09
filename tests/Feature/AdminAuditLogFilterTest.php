<?php

namespace Tests\Feature;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminAuditLogFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createUserWithRole('admin');
        $this->actor = $this->createUserWithRole('teacher', 'auditor@example.test');
    }

    public function test_admin_can_filter_audit_logs_by_date_range(): void
    {
        $oldTarget = User::factory()->create(['name' => 'Before Filter Student']);
        $newTarget = User::factory()->create(['name' => 'Within Filter Student']);

        $this->createEntry('role_changed', '2026-01-10 08:00:00', $oldTarget);
        $this->createEntry('password_reset', now()->format('Y-m-d H:i:s'), $newTarget);

        $this->actingAs($this->admin)
            ->get(route('admin.audit-logs.index', ['from' => now()->format('Y-m-d')]))
            ->assertOk()
            ->assertSee('Within Filter Student')
            ->assertDontSee('Before Filter Student');
    }

    public function test_admin_can_filter_audit_logs_by_action(): void
    {
        $roleTarget = User::factory()->create(['name' => 'Role Changed Student']);
        $suspendTarget = User::factory()->create(['name' => 'Suspended Student']);

        $this->createEntry('role_changed', now()->format('Y-m-d H:i:s'), $roleTarget);
        $this->createEntry('account_suspended', now()->format('Y-m-d H:i:s'), $suspendTarget);

        $this->actingAs($this->admin)
            ->get(route('admin.audit-logs.index', ['action' => 'role_changed']))
            ->assertOk()
            ->assertSee('Role Changed Student')
            ->assertDontSee('Suspended Student');
    }

    public function test_audit_log_details_are_rendered_with_labels(): void
    {
        $target = User::factory()->create(['name' => 'Audited Student']);

        $this->createEntry('role_changed', now()->format('Y-m-d H:i:s'), $target, [
            'from' => 'student',
            'to' => 'teacher',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.audit-logs.index'))
            ->assertOk()
            ->assertSee('Role diubah')
            ->assertSee('from student')
            ->assertSee('to teacher');
    }

    private function createEntry(string $action, string $createdAt, User $target, ?array $details = null): AdminAuditLog
    {
        $entry = AdminAuditLog::query()->create([
            'actor_user_id' => $this->actor->id,
            'actor_name' => $this->actor->name,
            'actor_email' => $this->actor->email,
            'target_user_id' => $target->id,
            'target_name' => $target->name,
            'target_email' => $target->email,
            'action' => $action,
            'details' => $details,
        ]);

        $entry->forceFill(['created_at' => Carbon::parse($createdAt)])->save();
        $entry->refresh();

        return $entry;
    }

    private function createUserWithRole(string $role, ?string $email = null): User
    {
        $user = User::factory()->create($email === null ? [] : ['email' => $email]);
        $user->forceFill(['role' => $role])->save();

        return $user;
    }
}
