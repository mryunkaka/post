<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    public function test_public_routes_are_rate_limited_after_sixty_requests_per_ip(): void
    {
        $ipAddress = '127.0.0.'.random_int(10, 250);

        Route::middleware('throttle:public')->get('/__test/public-throttle', static fn () => response()->noContent());

        $throttledAt = null;

        for ($attempt = 1; $attempt <= 61; $attempt++) {
            $response = $this->withServerVariables(['REMOTE_ADDR' => $ipAddress])
                ->get('/__test/public-throttle');

            if ($response->getStatusCode() === 429) {
                $throttledAt = $attempt;

                break;
            }

            $response->assertNoContent();
        }

        $this->assertNotNull($throttledAt);
        $this->assertContains($throttledAt, [60, 61]);
    }

    public function test_api_routes_are_rate_limited_after_thirty_requests_per_authenticated_user(): void
    {
        $user = (new User([
            'name' => 'API Tester',
            'email' => 'api-tester@example.com',
            'role' => 'editor',
            'is_active' => true,
        ]))->forceFill(['id' => random_int(10000, 99999)]);

        Route::middleware(['auth', 'throttle:api'])->get('/__test/api-throttle', static fn () => response()->noContent());

        $throttledAt = null;

        for ($attempt = 1; $attempt <= 31; $attempt++) {
            $response = $this->actingAs($user)->get('/__test/api-throttle');

            if ($response->getStatusCode() === 429) {
                $throttledAt = $attempt;

                break;
            }

            $response->assertNoContent();
        }

        $this->assertNotNull($throttledAt);
        $this->assertContains($throttledAt, [30, 31]);
    }
}
