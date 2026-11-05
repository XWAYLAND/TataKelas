<?php

namespace Tests\Feature;

use App\Models\Folder;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoTeacherTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_join_another_teachers_class_as_co_teacher_and_manage_content(): void
    {
        $owner = $this->userWithRole('teacher');
        $coTeacher = $this->userWithRole('teacher');
        $student = User::factory()->create();

        $classroom = $owner->teachingClassrooms()->create([
            'name' => 'Biology',
            'code' => 'BIO-404',
        ]);
        $classroom->students()->attach($student);

        $this->actingAs($coTeacher)
            ->post(route('classrooms.join'), ['code' => $classroom->code])
            ->assertRedirect(route('classrooms.show', $classroom))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('classroom_co_teacher', [
            'classroom_id' => $classroom->id,
            'user_id' => $coTeacher->id,
        ]);
        $this->assertTrue($classroom->hasTeacherAccess($coTeacher));

        $this->actingAs($coTeacher)
            ->get(route('classrooms.index'))
            ->assertOk()
            ->assertSee($classroom->name);

        $this->actingAs($coTeacher)
            ->get(route('classrooms.show', $classroom))
            ->assertOk();

        $this->actingAs($coTeacher)
            ->post(route('classrooms.folders.store', $classroom), [
                'name' => 'Co-teacher materials',
                'visibility' => 'class_material',
            ])
            ->assertRedirect();

        $folder = Folder::withoutGlobalScopes()
            ->where('classroom_id', $classroom->id)
            ->where('name', 'Co-teacher materials')
            ->firstOrFail();

        $this->assertSame($coTeacher->id, $folder->user_id);

        $this->actingAs($coTeacher)
            ->get(route('classrooms.folders.show', [$classroom, $folder]))
            ->assertOk();

        $this->actingAs($coTeacher)
            ->post(route('classrooms.folders.resources.store', [$classroom, $folder]), [
                'title' => 'Co-teacher resource',
                'resource_type' => 'url',
                'url' => 'https://example.com/co-teacher',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('resources', [
            'folder_id' => $folder->id,
            'title' => 'Co-teacher resource',
        ]);

        $assignment = $owner->folders()->create([
            'classroom_id' => $classroom->id,
            'name' => 'Co-teacher assignment',
            'visibility' => 'assignment',
        ]);

        $this->actingAs($coTeacher)
            ->get(route('classrooms.show', $classroom))
            ->assertOk()
            ->assertSee($assignment->name);

        $this->actingAs($coTeacher)
            ->get(route('classrooms.folders.show', [$classroom, $assignment]))
            ->assertOk();

        $submission = Submission::query()->create([
            'folder_id' => $assignment->id,
            'student_id' => $student->id,
            'submission_text' => 'My work',
            'status' => 'submitted',
        ]);

        $this->actingAs($coTeacher)
            ->patch(route('submissions.grade', $submission), [
                'grade' => 88,
                'teacher_feedback' => 'Good work.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'grade' => 88,
            'status' => 'graded',
        ]);
    }

    public function test_admin_can_join_a_class_as_co_teacher(): void
    {
        $owner = $this->userWithRole('teacher');
        $admin = $this->userWithRole('admin');

        $classroom = $owner->teachingClassrooms()->create([
            'name' => 'Chemistry',
            'code' => 'CHEM-101',
        ]);

        $this->actingAs($admin)
            ->post(route('classrooms.join'), ['code' => $classroom->code])
            ->assertRedirect(route('classrooms.show', $classroom))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('classroom_co_teacher', [
            'classroom_id' => $classroom->id,
            'user_id' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('classrooms.folders.store', $classroom), [
                'name' => 'Admin co-teacher folder',
                'visibility' => 'class_material',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('folders', [
            'classroom_id' => $classroom->id,
            'user_id' => $admin->id,
            'name' => 'Admin co-teacher folder',
        ]);
    }

    public function test_teacher_cannot_join_their_own_classroom_as_co_teacher(): void
    {
        $owner = $this->userWithRole('teacher');
        $classroom = $owner->teachingClassrooms()->create([
            'name' => 'Physics',
            'code' => 'PHYS-101',
        ]);

        $this->actingAs($owner)
            ->post(route('classrooms.join'), ['code' => $classroom->code])
            ->assertRedirect(route('classrooms.show', $classroom))
            ->assertSessionHas('info');

        $this->assertDatabaseMissing('classroom_co_teacher', [
            'classroom_id' => $classroom->id,
            'user_id' => $owner->id,
        ]);
    }

    public function test_owner_can_remove_a_co_teacher_and_revoke_classroom_access(): void
    {
        $owner = $this->userWithRole('teacher');
        $coTeacher = $this->userWithRole('teacher');
        $classroom = $owner->teachingClassrooms()->create([
            'name' => 'Geography',
            'code' => 'GEO-101',
        ]);
        $classroom->coTeachers()->attach($coTeacher);

        $this->actingAs($owner)
            ->delete(route('classrooms.co-teachers.destroy', [$classroom, $coTeacher]))
            ->assertRedirect(route('classrooms.show', $classroom))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('classroom_co_teacher', [
            'classroom_id' => $classroom->id,
            'user_id' => $coTeacher->id,
        ]);

        $this->actingAs($coTeacher)
            ->get(route('classrooms.show', $classroom))
            ->assertForbidden();
    }

    public function test_co_teacher_can_leave_a_classroom_they_joined(): void
    {
        $owner = $this->userWithRole('teacher');
        $coTeacher = $this->userWithRole('teacher');
        $classroom = $owner->teachingClassrooms()->create([
            'name' => 'Economics',
            'code' => 'ECO-101',
        ]);
        $classroom->coTeachers()->attach($coTeacher);

        $this->actingAs($coTeacher)
            ->delete(route('classrooms.co-teacher.leave', $classroom))
            ->assertRedirect(route('classrooms.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('classroom_co_teacher', [
            'classroom_id' => $classroom->id,
            'user_id' => $coTeacher->id,
        ]);

        $this->actingAs($coTeacher)
            ->get(route('classrooms.show', $classroom))
            ->assertForbidden();
    }

    public function test_co_teacher_cannot_remove_another_co_teacher_or_a_non_member(): void
    {
        $owner = $this->userWithRole('teacher');
        $coTeacher = $this->userWithRole('teacher');
        $otherCoTeacher = $this->userWithRole('teacher');
        $outsider = $this->userWithRole('teacher');
        $classroom = $owner->teachingClassrooms()->create([
            'name' => 'Philosophy',
            'code' => 'PHI-101',
        ]);
        $classroom->coTeachers()->attach([$coTeacher->id, $otherCoTeacher->id]);

        $this->actingAs($coTeacher)
            ->delete(route('classrooms.co-teachers.destroy', [$classroom, $otherCoTeacher]))
            ->assertForbidden();

        $this->actingAs($owner)
            ->delete(route('classrooms.co-teachers.destroy', [$classroom, $outsider]))
            ->assertForbidden();

        $this->assertDatabaseHas('classroom_co_teacher', [
            'classroom_id' => $classroom->id,
            'user_id' => $otherCoTeacher->id,
        ]);
    }

    public function test_student_join_still_uses_the_student_membership_table(): void
    {
        $owner = $this->userWithRole('teacher');
        $student = User::factory()->create();

        $classroom = $owner->teachingClassrooms()->create([
            'name' => 'History',
            'code' => 'HIST-101',
        ]);

        $this->actingAs($student)
            ->post(route('classrooms.join'), ['code' => $classroom->code])
            ->assertRedirect(route('classrooms.show', $classroom));

        $this->assertDatabaseHas('classroom_user', [
            'classroom_id' => $classroom->id,
            'user_id' => $student->id,
        ]);
        $this->assertDatabaseMissing('classroom_co_teacher', [
            'classroom_id' => $classroom->id,
            'user_id' => $student->id,
        ]);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role])->save();

        return $user;
    }
}
