<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWorkspaceAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_access_personal_workspace_pages_or_create_personal_content(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($admin)
            ->get(route('admin.index'))
            ->assertOk()
            ->assertSee('Kelola pengguna')
            ->assertSee('My classes')
            ->assertDontSee('My workspace')
            ->assertDontSee('Personal library')
            ->assertDontSee('All Resources')
            ->assertDontSee('Add Resource');

        foreach ([
            route('folders.index'),
            route('folders.create'),
            route('resources.index'),
            route('resources.create'),
            route('notes.index'),
            route('notes.create'),
            route('photos.index'),
            route('photos.create'),
        ] as $url) {
            $this->get($url)->assertForbidden();
        }

        $this->post(route('folders.store'))->assertForbidden();
        $this->post(route('resources.store'))->assertForbidden();
        $this->post(route('notes.store'))->assertForbidden();
        $this->post(route('photos.store'))->assertForbidden();
    }

    public function test_admin_home_redirects_to_the_admin_directory_and_classrooms_remain_available(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($admin)
            ->get('/')
            ->assertRedirect(route('admin.index'));

        $this->get(route('classrooms.index'))->assertOk();
    }
}
