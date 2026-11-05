<?php

namespace Tests\Feature;

use App\Models\Folder;
use App\Models\Resource;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassroomVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_private_folders_are_owner_only_and_class_folders_require_enrollment(): void
    {
        $teacher = User::factory()->create();
        $teacher->forceFill(['role' => 'teacher'])->save();
        $student = User::factory()->create();
        $outsider = User::factory()->create();

        $classroom = $teacher->teachingClassrooms()->create([
            'name' => 'Biology',
            'code' => 'BIO-101',
        ]);
        $classroom->students()->attach($student);

        $privateFolder = $student->folders()->create([
            'name' => 'Private notes',
            'visibility' => 'private',
        ]);
        $classFolder = Folder::withoutGlobalScopes()->create([
            'user_id' => $teacher->id,
            'classroom_id' => $classroom->id,
            'name' => 'Course materials',
            'visibility' => 'class_material',
        ]);
        $assignment = Folder::withoutGlobalScopes()->create([
            'user_id' => $teacher->id,
            'classroom_id' => $classroom->id,
            'name' => 'Assignment',
            'visibility' => 'assignment',
        ]);
        $classResource = Resource::withoutGlobalScopes()->create([
            'folder_id' => $classFolder->id,
            'title' => 'Course link',
            'url' => 'https://example.com/course',
            'resource_type' => 'url',
        ]);

        $this->actingAs($teacher);
        $this->assertFalse(Folder::query()->whereKey($privateFolder)->exists());
        $this->assertTrue(Folder::query()->whereKey($classFolder)->exists());
        $this->assertTrue(Resource::query()->whereKey($classResource)->exists());

        $this->actingAs($student);
        $this->assertTrue(Folder::query()->whereKey($privateFolder)->exists());
        $this->assertTrue(Folder::query()->whereKey($classFolder)->exists());
        $this->assertTrue(Folder::query()->whereKey($assignment)->exists());
        $this->assertTrue(Resource::query()->whereKey($classResource)->exists());
        $this->patch(route('resources.update', $classResource), [
            'folder_id' => $privateFolder->id,
            'title' => 'Attempted change',
            'resource_type' => 'url',
            'url' => 'https://example.com',
            'read_status' => 'to_read',
        ])->assertForbidden();

        $this->actingAs($outsider);
        $this->assertFalse(Folder::query()->whereKey($privateFolder)->exists());
        $this->assertFalse(Folder::query()->whereKey($classFolder)->exists());
        $this->assertFalse(Folder::query()->whereKey($assignment)->exists());
        $this->assertFalse(Resource::query()->whereKey($classResource)->exists());
    }

    public function test_resource_visibility_follows_its_folder_visibility(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $privateFolder = $owner->folders()->create([
            'name' => 'Private',
            'visibility' => 'private',
        ]);
        $resource = Resource::withoutGlobalScopes()->create([
            'folder_id' => $privateFolder->id,
            'title' => 'Private link',
            'url' => 'https://example.com',
            'resource_type' => 'url',
        ]);

        $this->actingAs($otherUser);

        $this->assertFalse(Resource::query()->whereKey($resource)->exists());
    }

    public function test_student_can_submit_and_teacher_can_grade_an_assignment(): void
    {
        $teacher = User::factory()->create();
        $teacher->forceFill(['role' => 'teacher'])->save();
        $student = User::factory()->create();
        $classroom = $teacher->teachingClassrooms()->create([
            'name' => 'Biology',
            'code' => 'BIO-202',
        ]);
        $classroom->students()->attach($student);
        $assignment = Folder::withoutGlobalScopes()->create([
            'user_id' => $teacher->id,
            'classroom_id' => $classroom->id,
            'name' => 'Lab report',
            'visibility' => 'assignment',
            'due_date' => now()->subMinute(),
        ]);

        $this->assertSame('student', $student->role);
        $this->assertTrue($classroom->students()->whereKey($student->id)->exists());
        $this->assertTrue($student->can('submit', $assignment));

        $this->actingAs($student)
            ->post(route('folders.submissions.store', $assignment), [
                'submission_text' => 'Results and observations.',
            ])
            ->assertRedirect(route('classrooms.folders.show', [$classroom, $assignment]));

        $submission = Submission::where('folder_id', $assignment->id)->firstOrFail();
        $this->assertSame('late', $submission->status);

        $this->actingAs($teacher)
            ->patch(route('submissions.grade', $submission), [
                'grade' => 92,
                'teacher_feedback' => 'Clear analysis.',
            ])
            ->assertRedirect(route('classrooms.folders.show', [$classroom, $assignment]));

        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'grade' => 92,
            'status' => 'graded',
        ]);

        $outsider = User::factory()->create();
        $this->actingAs($outsider)
            ->patch(route('submissions.grade', $submission), [
                'grade' => 100,
            ])
            ->assertForbidden();
    }

    public function test_admin_can_teach_a_class_but_cannot_read_student_private_folders(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $student = User::factory()->create();
        $privateFolder = $student->folders()->create([
            'name' => 'Personal study notes',
            'visibility' => 'private',
        ]);
        $classroom = $admin->teachingClassrooms()->create([
            'name' => 'Admin-led class',
            'code' => 'ADM-101',
        ]);
        $classFolder = Folder::withoutGlobalScopes()->create([
            'user_id' => $admin->id,
            'classroom_id' => $classroom->id,
            'name' => 'Shared lesson',
            'visibility' => 'class_material',
        ]);

        $this->actingAs($admin);

        $this->assertTrue(Folder::query()->whereKey($classFolder)->exists());
        $this->assertFalse(Folder::query()->whereKey($privateFolder)->exists());
    }
}
