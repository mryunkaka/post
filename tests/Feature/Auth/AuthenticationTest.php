<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_login_route_is_rate_limited_after_ten_requests_per_ip(): void
    {
        RateLimiter::clear('127.0.0.1');

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->from('/login')
                ->post('/login', [
                    'email' => 'missing-user@local.test',
                    'password' => 'wrong-password',
                ])
                ->assertSessionHasErrors('email');
        }

        $this->from('/login')
            ->post('/login', [
                'email' => 'missing-user@local.test',
                'password' => 'wrong-password',
            ])
            ->assertStatus(429);
    }
}
