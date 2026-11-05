<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_password_change_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('account.password.edit'))
            ->assertOk()
            ->assertSee('Ganti kata sandi')
            ->assertSee('Kata sandi saat ini')
            ->assertSee('Konfirmasi kata sandi baru');
    }

    public function test_user_can_change_password_after_confirming_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'old-password',
        ]);

        $this->actingAs($user)
            ->put(route('account.password.update'), [
                'current_password' => 'old-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect(route('account.password.edit'))
            ->assertSessionHas('success', 'Kata sandi berhasil diperbarui.');

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $this->assertFalse(Hash::check('old-password', $user->fresh()->password));
    }

    public function test_password_cannot_be_changed_with_incorrect_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'old-password',
        ]);

        $this->actingAs($user)
            ->from(route('account.password.edit'))
            ->put(route('account.password.update'), [
                'current_password' => 'incorrect-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect(route('account.password.edit'))
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    public function test_new_password_must_be_confirmed_and_differ_from_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'old-password',
        ]);

        $this->actingAs($user)
            ->from(route('account.password.edit'))
            ->put(route('account.password.update'), [
                'current_password' => 'old-password',
                'password' => 'old-password',
                'password_confirmation' => 'old-password',
            ])
            ->assertRedirect(route('account.password.edit'))
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    public function test_new_password_confirmation_must_match(): void
    {
        $user = User::factory()->create([
            'password' => 'old-password',
        ]);

        $this->actingAs($user)
            ->from(route('account.password.edit'))
            ->put(route('account.password.update'), [
                'current_password' => 'old-password',
                'password' => 'new-password',
                'password_confirmation' => 'different-password',
            ])
            ->assertRedirect(route('account.password.edit'))
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }
}
