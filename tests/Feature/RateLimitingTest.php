<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_routes_are_rate_limited_after_sixty_requests_per_ip(): void
    {
        Route::middleware('throttle:public')->get('/__test/public-throttle', static fn () => response()->noContent());

        for ($attempt = 0; $attempt < 60; $attempt++) {
            $this->get('/__test/public-throttle')->assertNoContent();
        }

        $this->get('/__test/public-throttle')->assertStatus(429);
    }

    public function test_api_routes_are_rate_limited_after_thirty_requests_per_authenticated_user(): void
    {
        $user = User::factory()->create();

        Route::middleware(['auth', 'throttle:api'])->get('/__test/api-throttle', static fn () => response()->noContent());

        for ($attempt = 0; $attempt < 30; $attempt++) {
            $this->actingAs($user)->get('/__test/api-throttle')->assertNoContent();
        }

        $this->actingAs($user)->get('/__test/api-throttle')->assertStatus(429);
    }
}
