<?php

namespace Tests\Feature;

use App\Models\Resource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResourceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_resource_can_be_updated_without_replacing_its_files(): void
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
            'title' => 'Original title',
            'resource_type' => 'file',
            'read_status' => 'to_read',
        ]);
        $file = $resource->files()->create([
            'file_path' => 'personal-resources/guide.pdf',
            'original_name' => 'guide.pdf',
            'size' => 20,
        ]);
        Storage::disk('local')->put($file->file_path, '%PDF-1.4 fake content');

        $this->actingAs($user)
            ->put(route('resources.update', $resource), [
                'folder_id' => $folder->id,
                'title' => 'Updated title',
                'url' => '',
                'resource_type' => 'file',
                'read_status' => 'reading',
            ])
            ->assertRedirect(route('folders.show', $folder));

        $resource->refresh()->load('files');

        $this->assertSame('Updated title', $resource->title);
        $this->assertSame(1, $resource->files->count());
        $this->assertSame('personal-resources/guide.pdf', $resource->files->first()->file_path);
        Storage::disk('local')->assertExists($file->file_path);
    }
}
