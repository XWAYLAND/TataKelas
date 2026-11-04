<?php

namespace Tests\Feature;

use App\Models\Folder;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_registered_user_claims_existing_folders(): void
    {
        $legacyFolder = Folder::withoutGlobalScopes()->create([
            'name' => 'Legacy folder',
            'description' => null,
            'color_code' => '#3B82F6',
        ]);

        $this->post(route('register'), [
            'name' => 'First User',
            'email' => 'first@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('folders.index'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('folders', [
            'id' => $legacyFolder->id,
            'user_id' => User::where('email', 'first@example.com')->value('id'),
        ]);
    }

    public function test_users_cannot_view_another_users_folder_or_resource(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherFolder = $otherUser->folders()->create([
            'name' => 'Private folder',
            'color_code' => '#3B82F6',
        ]);
        $otherResource = Resource::withoutGlobalScopes()->create([
            'folder_id' => $otherFolder->id,
            'title' => 'Private resource',
            'url' => 'https://example.com',
            'resource_type' => 'article',
            'read_status' => 'to_read',
        ]);

        $this->actingAs($owner)
            ->get(route('folders.show', $otherFolder))
            ->assertRedirect(route('folders.index'));

        $this->actingAs($owner)
            ->get(route('resources.show', $otherResource))
            ->assertRedirect(route('resources.index'));

        $this->actingAs($owner)
            ->get(route('folders.index'))
            ->assertOk()
            ->assertDontSee('Private folder');
    }

    public function test_resource_cannot_be_created_in_another_users_folder(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherFolder = $otherUser->folders()->create([
            'name' => 'Other folder',
            'color_code' => '#3B82F6',
        ]);

        $this->actingAs($owner)
            ->post(route('resources.store'), [
                'folder_id' => $otherFolder->id,
                'title' => 'Attempted resource',
                'url' => 'https://example.com',
                'resource_type' => 'article',
                'read_status' => 'to_read',
            ])
            ->assertSessionHasErrors('folder_id');

        $this->assertDatabaseMissing('resources', [
            'title' => 'Attempted resource',
        ]);
    }
}
