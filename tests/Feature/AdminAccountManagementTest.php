<?php

namespace Tests\Feature;

use App\Models\AdminAuditLog;
use App\Models\Folder;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAccountManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_directory_and_audit_log_are_restricted_to_admins(): void
    {
        $admin = $this->createUserWithRole('admin');
        $student = User::factory()->create();

        $this->get(route('admin.index'))->assertRedirect(route('login'));
        $this->actingAs($student)->get(route('admin.index'))->assertForbidden();
        $this->actingAs($student)->get(route('admin.audit-logs.index'))->assertForbidden();

        $this->actingAs($admin)
            ->get(route('admin.index'))
            ->assertOk()
            ->assertSee('Kelola pengguna')
            ->assertSee($student->email);
    }

    public function test_admin_can_search_and_filter_the_user_directory(): void
    {
        $admin = $this->createUserWithRole('admin');
        $teacher = $this->createUserWithRole('teacher', 'guru@example.test');
        $student = User::factory()->create(['email' => 'murid@example.test']);

        $this->actingAs($admin)
            ->get(route('admin.index', ['q' => 'guru', 'role' => 'teacher']))
            ->assertOk()
            ->assertSee($teacher->email)
            ->assertDontSee($student->email);
    }

    public function test_admin_can_change_roles_and_the_change_is_audited(): void
    {
        $admin = $this->createUserWithRole('admin');
        $student = User::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $student), ['role' => 'teacher'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('teacher', $student->fresh()->role);
        $this->assertDatabaseHas('admin_audit_logs', [
            'actor_user_id' => $admin->id,
            'target_user_id' => $student->id,
            'action' => AdminAuditLog::ROLE_CHANGED,
        ]);
        $this->assertSame(
            ['from' => 'student', 'to' => 'teacher'],
            AdminAuditLog::query()->where('action', AdminAuditLog::ROLE_CHANGED)->firstOrFail()->details,
        );
    }

    public function test_admin_cannot_change_own_role_or_demote_a_teacher_with_classrooms_to_student(): void
    {
        $admin = $this->createUserWithRole('admin');
        $teacher = $this->createUserWithRole('teacher');
        $teacher->teachingClassrooms()->create([
            'name' => 'Biology',
            'code' => 'BIO-401',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $admin), ['role' => 'teacher'])
            ->assertSessionHasErrors('role');

        $this->delete(route('admin.users.destroy', $admin))
            ->assertSessionHasErrors('account');

        $this->patch(route('admin.users.role', $teacher), ['role' => 'student'])
            ->assertSessionHasErrors('role');

        $this->assertSame('admin', $admin->fresh()->role);
        $this->assertSame('teacher', $teacher->fresh()->role);
        $this->assertDatabaseCount('admin_audit_logs', 0);
    }

    public function test_admin_reset_generates_a_temporary_password_without_logging_it(): void
    {
        $admin = $this->createUserWithRole('admin');
        $student = User::factory()->create([
            'password' => 'previous-password',
            'remember_token' => 'old-remember-token',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.users.reset-password', $student))
            ->assertRedirect(route('admin.index'))
            ->assertSessionHas('temporary_password');

        $temporaryPassword = $response->getSession()->get('temporary_password');

        $this->assertIsString($temporaryPassword);
        $this->assertSame(24, strlen($temporaryPassword));
        $this->assertTrue(Hash::check($temporaryPassword, $student->fresh()->password));
        $this->assertNotSame('old-remember-token', $student->fresh()->remember_token);

        $auditLog = AdminAuditLog::query()->where('action', AdminAuditLog::PASSWORD_RESET)->firstOrFail();
        $this->assertSame($student->email, $auditLog->target_email);
        $this->assertStringNotContainsString($temporaryPassword, json_encode($auditLog->details));

        $this->get(route('admin.index'))->assertOk()->assertSee($temporaryPassword);
        $this->get(route('admin.index'))->assertOk()->assertDontSee($temporaryPassword);
    }

    public function test_admin_cannot_delete_users_with_related_learning_data(): void
    {
        $admin = $this->createUserWithRole('admin');
        $student = User::factory()->create();
        $student->folders()->create(['name' => 'Personal work']);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $student))
            ->assertSessionHasErrors('account');

        $this->assertDatabaseHas('users', ['id' => $student->id]);
        $this->assertDatabaseCount('admin_audit_logs', 0);
    }

    public function test_admin_cannot_delete_users_who_teach_or_participate_in_classrooms(): void
    {
        $admin = $this->createUserWithRole('admin');
        $teacher = $this->createUserWithRole('teacher');
        $teacherClassroom = $teacher->teachingClassrooms()->create([
            'name' => 'Teacher class',
            'code' => 'TCH-401',
        ]);
        $student = User::factory()->create();
        $studentClassroom = $teacher->teachingClassrooms()->create([
            'name' => 'Student class',
            'code' => 'STU-401',
        ]);
        $studentClassroom->students()->attach($student);

        $this->actingAs($admin);

        $this->delete(route('admin.users.destroy', $teacher))
            ->assertSessionHasErrors('account');
        $this->delete(route('admin.users.destroy', $student))
            ->assertSessionHasErrors('account');

        $this->assertDatabaseHas('classrooms', ['id' => $teacherClassroom->id]);
        $this->assertDatabaseHas('users', ['id' => $teacher->id]);
        $this->assertDatabaseHas('users', ['id' => $student->id]);
    }

    public function test_admin_cannot_delete_a_user_with_submissions(): void
    {
        $admin = $this->createUserWithRole('admin');
        $teacher = $this->createUserWithRole('teacher');
        $student = User::factory()->create();
        $classroom = $teacher->teachingClassrooms()->create([
            'name' => 'Assignment class',
            'code' => 'ASN-401',
        ]);
        $assignment = Folder::withoutGlobalScopes()->create([
            'user_id' => $teacher->id,
            'classroom_id' => $classroom->id,
            'name' => 'Assignment',
            'visibility' => 'assignment',
        ]);
        $submission = Submission::query()->create([
            'folder_id' => $assignment->id,
            'student_id' => $student->id,
            'submission_text' => 'Submitted work',
            'status' => 'submitted',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $student))
            ->assertSessionHasErrors('account');

        $this->assertDatabaseHas('submissions', ['id' => $submission->id]);
        $this->assertDatabaseHas('users', ['id' => $student->id]);
    }

    public function test_admin_can_delete_an_unlinked_account_and_audit_keeps_its_snapshot(): void
    {
        $admin = $this->createUserWithRole('admin');
        $student = User::factory()->create([
            'name' => 'Unlinked Student',
            'email' => 'unlinked@example.test',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $student))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $student->id]);
        $this->assertDatabaseHas('admin_audit_logs', [
            'actor_user_id' => $admin->id,
            'target_user_id' => null,
            'target_name' => 'Unlinked Student',
            'target_email' => 'unlinked@example.test',
            'action' => AdminAuditLog::ACCOUNT_DELETED,
        ]);
    }

    public function test_admin_can_review_audit_log_filters(): void
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
            'details' => ['method' => 'generated_temporary_password'],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.audit-logs.index', [
                'q' => $student->email,
                'action' => AdminAuditLog::PASSWORD_RESET,
            ]))
            ->assertOk()
            ->assertSee($student->email)
            ->assertSee('Password direset');
    }

    private function createUserWithRole(string $role, ?string $email = null): User
    {
        $user = User::factory()->create($email === null ? [] : ['email' => $email]);
        $user->forceFill(['role' => $role])->save();

        return $user;
    }
}
