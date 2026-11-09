<?php

namespace Tests\Feature;

use App\Models\Resource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResourcePreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdf_material_is_streamed_inline_for_preview(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $file = $this->fileResourceFile($user, 'personal-resources/guide.pdf');
        Storage::disk('local')->put($file->file_path, '%PDF-1.4 fake content');

        $response = $this->actingAs($user)->get(route('resources.files.preview', [$file->resource, $file]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition'));
    }

    public function test_non_pdf_materials_are_not_previewed_inline(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $file = $this->fileResourceFile($user, 'personal-resources/guide.docx');
        Storage::disk('local')->put($file->file_path, 'fake document');

        $this->actingAs($user)
            ->get(route('resources.files.preview', [$file->resource, $file]))
            ->assertNotFound();
    }

    public function test_other_users_cannot_preview_private_materials(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $resource = Resource::withoutGlobalScopes()->create([
            'folder_id' => $owner->folders()->create([
                'name' => 'Study materials',
                'color_code' => '#3B82F6',
                'visibility' => 'private',
            ])->id,
            'title' => 'Guide',
            'resource_type' => 'file',
            'read_status' => 'to_read',
        ]);
        $file = $resource->files()->create([
            'file_path' => 'personal-resources/private.pdf',
            'original_name' => 'private.pdf',
            'size' => 24,
        ]);
        Storage::disk('local')->put($file->file_path, '%PDF-1.4 private content');

        $this->actingAs($otherUser)
            ->get(route('resources.files.preview', ['resource' => $resource, 'file' => $file]))
            ->assertNotFound();
    }

    private function fileResourceFile(User $owner, string $path)
    {
        $folder = $owner->folders()->create([
            'name' => 'Study materials',
            'color_code' => '#3B82F6',
            'visibility' => 'private',
        ]);

        $resource = Resource::withoutGlobalScopes()->create([
            'folder_id' => $folder->id,
            'title' => 'Guide',
            'resource_type' => 'file',
            'read_status' => 'to_read',
        ]);

        return $resource->files()->create([
            'file_path' => $path,
            'original_name' => basename($path),
            'size' => 20,
        ]);
    }
}
