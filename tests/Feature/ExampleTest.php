<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_guest_can_view_the_welcome_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Workspace murid dan sistem belajar')
            ->assertSee('TataKelas')
            ->assertSee(route('register'));
    }

    public function test_authenticated_user_is_redirected_to_the_dashboard(): void
    {
        $this->actingAs(new User(['name' => 'Test User', 'email' => 'test@example.com']))
            ->get('/')
            ->assertRedirect(route('folders.index'));
    }

    public function test_guest_can_open_authentication_pages(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Masuk ke TataKelas');
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('TataKelas');
        $this->get(route('account.password.edit'))
            ->assertRedirect(route('login'));
    }
}
