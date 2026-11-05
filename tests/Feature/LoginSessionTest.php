<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Cookie;
use Tests\TestCase;

class LoginSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_cookie_expires_when_browser_closes_without_remember_me(): void
    {
        config(['session.expire_on_close' => true]);

        $user = User::factory()->create([
            'password' => 'correct-password',
        ]);
        $csrfToken = 'session-test-token';

        $response = $this->withSession(['_token' => $csrfToken])
            ->post(route('login'), [
                '_token' => $csrfToken,
                'email' => $user->email,
                'password' => 'correct-password',
            ]);

        $response->assertRedirect(route('folders.index'));

        $sessionCookie = $response->getCookie(config('session.cookie'));

        $this->assertInstanceOf(Cookie::class, $sessionCookie);
        $this->assertSame(0, $sessionCookie->getExpiresTime());
        $this->assertFalse($sessionCookie->isRaw());
        $this->assertFalse(collect($response->headers->getCookies())
            ->contains(fn (Cookie $cookie) => str_starts_with($cookie->getName(), 'remember_')));
    }

    public function test_remember_me_adds_a_persistent_recaller_cookie(): void
    {
        config(['session.expire_on_close' => true]);

        $user = User::factory()->create([
            'password' => 'correct-password',
        ]);
        $csrfToken = 'remember-test-token';

        $response = $this->withSession(['_token' => $csrfToken])
            ->post(route('login'), [
                '_token' => $csrfToken,
                'email' => $user->email,
                'password' => 'correct-password',
                'remember' => '1',
            ]);

        $response->assertRedirect(route('folders.index'));

        $recallerCookie = collect($response->headers->getCookies())
            ->first(fn (Cookie $cookie) => str_starts_with($cookie->getName(), 'remember_'));

        $this->assertInstanceOf(Cookie::class, $recallerCookie);
        $this->assertGreaterThan(now()->getTimestamp(), $recallerCookie->getExpiresTime());
    }

    public function test_admin_login_defaults_to_the_admin_directory(): void
    {
        $admin = User::factory()->create([
            'password' => 'correct-password',
        ]);
        $admin->forceFill(['role' => 'admin'])->save();

        $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'correct-password',
        ])->assertRedirect(route('admin.index'));
    }
}
