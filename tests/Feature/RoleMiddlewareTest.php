<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'role:admin'])
            ->get('/test-admin-only', fn () => 'ok');
    }

    public function test_admin_can_access_admin_only_route(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get('/test-admin-only')
            ->assertOk()
            ->assertSee('ok');
    }

    public function test_non_admin_is_forbidden_from_admin_only_route(): void
    {
        $editor = User::factory()->create([
            'role' => 'editor',
        ]);

        $this->actingAs($editor)
            ->get('/test-admin-only')
            ->assertForbidden();
    }
}
