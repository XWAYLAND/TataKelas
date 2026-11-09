<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Folder;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_submit_multiple_files_and_download_them(): void
    {
        Storage::fake('local');

        [$teacher, $student, $classroom, $assignment] = $this->assignmentContext();

        $this->actingAs($student)
            ->post(route('folders.submissions.store', $assignment), [
                'submission_text' => 'Here are my files.',
                'files' => [
                    UploadedFile::fake()->create('report.pdf', 120, 'application/pdf'),
                    UploadedFile::fake()->create('archive.zip', 240, 'application/zip'),
                ],
            ])
            ->assertRedirect(route('classrooms.folders.show', [$classroom, $assignment]));

        $submission = Submission::query()->where('folder_id', $assignment->id)->firstOrFail();
        $this->assertCount(2, $submission->files);
        Storage::disk('local')->assertExists($submission->files[0]->file_path);
        Storage::disk('local')->assertExists($submission->files[1]->file_path);

        $this->actingAs($student)
            ->get(route('submissions.files.download', $submission->files[0]))
            ->assertOk()
            ->assertDownload('report.pdf');

        $this->actingAs($teacher)
            ->get(route('submissions.files.download', $submission->files[1]))
            ->assertOk()
            ->assertDownload('archive.zip');
    }

    public function test_updating_a_submission_replaces_its_attachments(): void
    {
        Storage::fake('local');

        [, $student, , $assignment] = $this->assignmentContext();

        $this->actingAs($student)->post(route('folders.submissions.store', $assignment), [
            'files' => [
                UploadedFile::fake()->create('first.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->create('second.pdf', 100, 'application/pdf'),
            ],
        ]);

        $submission = Submission::query()->where('folder_id', $assignment->id)->firstOrFail();
        $oldPaths = $submission->files->pluck('file_path')->all();

        $this->actingAs($student)->post(route('folders.submissions.store', $assignment), [
            'submission_text' => 'Updated work.',
            'files' => [
                UploadedFile::fake()->create('replacement.pdf', 100, 'application/pdf'),
            ],
        ]);

        $submission->refresh();
        $this->assertCount(1, $submission->files);
        $this->assertSame('replacement.pdf', $submission->files[0]->original_name);

        foreach ($oldPaths as $oldPath) {
            Storage::disk('local')->assertMissing($oldPath);
        }
    }

    public function test_student_can_unsend_a_submission_and_its_files_are_deleted(): void
    {
        Storage::fake('local');

        [, $student, $classroom, $assignment] = $this->assignmentContext();

        $this->actingAs($student)->post(route('folders.submissions.store', $assignment), [
            'files' => [
                UploadedFile::fake()->create('work.pdf', 100, 'application/pdf'),
            ],
        ]);

        $submission = Submission::query()->where('folder_id', $assignment->id)->firstOrFail();
        $filePath = $submission->files[0]->file_path;
        Storage::disk('local')->assertExists($filePath);

        $this->actingAs($student)
            ->delete(route('folders.submissions.destroy', $assignment))
            ->assertRedirect(route('classrooms.folders.show', [$classroom, $assignment]));

        $this->assertDatabaseMissing('submissions', ['id' => $submission->id]);
        $this->assertDatabaseMissing('submission_files', ['submission_id' => $submission->id]);
        Storage::disk('local')->assertMissing($filePath);
    }

    /**
     * @return array{0: User, 1: User, 2: Classroom, 3: Folder}
     */
    private function assignmentContext(): array
    {
        $teacher = User::factory()->create();
        $teacher->forceFill(['role' => 'teacher'])->save();
        $student = User::factory()->create();

        $classroom = $teacher->teachingClassrooms()->create([
            'name' => 'Biology',
            'code' => 'BIO-404',
        ]);
        $classroom->students()->attach($student);

        $assignment = Folder::withoutGlobalScopes()->create([
            'user_id' => $teacher->id,
            'classroom_id' => $classroom->id,
            'name' => 'Lab report',
            'visibility' => 'assignment',
        ]);

        return [$teacher, $student, $classroom, $assignment];
    }
}
