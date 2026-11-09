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

    public function test_document_resource_can_be_updated_without_replacing_its_file(): void
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
            'file_path' => 'personal-resources/guide.pdf',
            'resource_type' => 'file',
            'read_status' => 'to_read',
        ]);
        Storage::disk('local')->put($resource->file_path, '%PDF-1.4 fake content');

        $this->actingAs($user)
            ->put(route('resources.update', $resource), [
                'folder_id' => $folder->id,
                'title' => 'Updated title',
                'url' => '',
                'resource_type' => 'file',
                'read_status' => 'reading',
            ])
            ->assertRedirect(route('folders.show', $folder));

        $resource->refresh();

        $this->assertSame('Updated title', $resource->title);
        $this->assertSame('personal-resources/guide.pdf', $resource->file_path);
        Storage::disk('local')->assertExists($resource->file_path);
    }
}
