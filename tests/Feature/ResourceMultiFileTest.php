<?php

namespace Tests\Feature;

use App\Models\Folder;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResourceMultiFileTest extends TestCase
{
    use RefreshDatabase;

    public function test_personal_resource_accepts_multiple_files(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $folder = $user->folders()->create([
            'name' => 'Study materials',
            'color_code' => '#3B82F6',
            'visibility' => 'private',
        ]);

        $response = $this->actingAs($user)->post(route('resources.store'), [
            'folder_id' => $folder->id,
            'title' => 'Chapter bundle',
            'url' => '',
            'resource_type' => 'file',
            'read_status' => 'to_read',
            'files' => [
                UploadedFile::fake()->create('slides.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->create('summary.docx', 50),
            ],
        ]);

        $response->assertRedirect(route('folders.show', $folder));
        $this->assertDatabaseCount('resource_files', 2);

        $resource = Resource::withoutGlobalScopes()->where('title', 'Chapter bundle')->firstOrFail();
        $files = $resource->files()->orderBy('id')->get();

        $this->assertSame(2, $files->count());
        $this->assertSame('slides.pdf', $files[0]->original_name);
        $this->assertSame('summary.docx', $files[1]->original_name);
        Storage::disk('local')->assertExists($files[0]->file_path);
        Storage::disk('local')->assertExists($files[1]->file_path);
        $this->assertTrue(str_starts_with($files[0]->file_path, 'personal-resources/'));
    }

    public function test_updating_a_resource_replaces_every_attached_file(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $folder = $user->folders()->create([
            'name' => 'Study materials',
            'color_code' => '#3B82F6',
            'visibility' => 'private',
        ]);
        $resource = Resource::withoutGlobalScopes()->create([
            'folder_id' => $folder->id,
            'title' => 'Chapter bundle',
            'resource_type' => 'file',
            'read_status' => 'to_read',
        ]);
        $first = $resource->files()->create([
            'file_path' => 'personal-resources/guide.pdf',
            'original_name' => 'guide.pdf',
            'size' => 20,
        ]);
        Storage::disk('local')->put($first->file_path, '%PDF-1.4 fake');

        $response = $this->actingAs($user)->put(route('resources.update', $resource), [
            'folder_id' => $folder->id,
            'title' => 'Chapter bundle',
            'url' => '',
            'resource_type' => 'file',
            'read_status' => 'reading',
            'files' => [
                UploadedFile::fake()->create('replacement.pdf', 10, 'application/pdf'),
            ],
        ]);

        $response->assertRedirect(route('folders.show', $folder));

        $resource->refresh()->load('files');

        $this->assertSame(1, $resource->files->count());
        $this->assertSame('replacement.pdf', $resource->files->first()->original_name);
        Storage::disk('local')->assertMissing($first->file_path);
    }

    public function test_classroom_teacher_can_share_a_multi_file_resource(): void
    {
        Storage::fake('local');

        $teacher = User::factory()->create(['role' => 'teacher']);
        $classroom = $teacher->teachingClassrooms()->create([
            'name' => 'Biology',
            'code' => 'BIO-101',
        ]);
        $folder = Folder::withoutGlobalScopes()->create([
            'user_id' => $teacher->id,
            'classroom_id' => $classroom->id,
            'name' => 'Assignment instructions',
            'visibility' => 'assignment',
            'color_code' => '#22C55E',
        ]);

        $response = $this->actingAs($teacher)->post(
            route('classrooms.folders.resources.store', [$classroom, $folder]),
            [
                'title' => 'Lab package',
                'resource_type' => 'file',
                'url' => '',
                'notes' => 'Read before the lab session.',
                'files' => [
                    UploadedFile::fake()->create('worksheet.pdf', 100, 'application/pdf'),
                    UploadedFile::fake()->create('rubric.pptx', 80),
                    UploadedFile::fake()->create('example.doc', 40),
                ],
            ]
        );

        $response->assertRedirect(route('classrooms.folders.show', [$classroom, $folder]));
        $this->assertDatabaseCount('resource_files', 3);

        $resource = $folder->resources()->first();
        $files = $resource->files()->orderBy('id')->get();

        $this->assertSame(3, $files->count());
        $this->assertSame(['worksheet.pdf', 'rubric.pptx', 'example.doc'], $files->pluck('original_name')->all());
        foreach ($files as $file) {
            Storage::disk('local')->assertExists($file->file_path);
            $this->assertTrue(str_starts_with($file->file_path, 'class-materials/'));
        }
    }

    public function test_file_resources_without_files_are_rejected(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $folder = $user->folders()->create([
            'name' => 'Study materials',
            'color_code' => '#3B82F6',
            'visibility' => 'private',
        ]);

        $this->actingAs($user)->post(route('resources.store'), [
            'folder_id' => $folder->id,
            'title' => 'Chapter bundle',
            'url' => '',
            'resource_type' => 'file',
            'read_status' => 'to_read',
        ])->assertSessionHasErrors('files');

        $this->assertDatabaseEmpty('resources');
        $this->assertDatabaseEmpty('resource_files');
    }

    public function test_students_can_download_class_files_but_not_outsiders(): void
    {
        Storage::fake('local');

        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create();
        $outsider = User::factory()->create();
        $classroom = $teacher->teachingClassrooms()->create([
            'name' => 'Biology',
            'code' => 'BIO-101',
        ]);
        $classroom->students()->attach($student);

        $folder = Folder::withoutGlobalScopes()->create([
            'user_id' => $teacher->id,
            'classroom_id' => $classroom->id,
            'name' => 'Learning materials',
            'visibility' => 'class_material',
            'color_code' => '#22C55E',
        ]);
        $resource = $folder->resources()->create([
            'title' => 'Slides',
            'resource_type' => 'file',
            'read_status' => 'to_read',
        ]);
        $file = $resource->files()->create([
            'file_path' => 'class-materials/slides.pdf',
            'original_name' => 'slides.pdf',
            'size' => 20,
        ]);
        Storage::disk('local')->put($file->file_path, '%PDF-1.4 fake');

        $this->actingAs($student)
            ->get(route('resources.files.download', [$resource, $file]))
            ->assertOk();

        $this->actingAs($outsider)
            ->get(route('resources.files.download', [$resource, $file]))
            ->assertNotFound();
    }
}
