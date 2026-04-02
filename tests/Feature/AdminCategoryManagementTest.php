<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_create_category(): void
    {
        $editor = User::factory()->create([
            'role' => 'editor',
        ]);

        $response = $this->actingAs($editor)->post(route('admin.categories.store'), [
            'name' => 'Opini',
            'slug' => '',
            'parent_id' => null,
            'description' => 'Kanal opini redaksi.',
            'sort_order' => 5,
            'is_active' => true,
            'seo_title' => 'Opini',
            'seo_description' => 'Kanal opini redaksi.',
        ]);

        $category = Category::firstOrFail();

        $response->assertRedirect(route('admin.categories.edit', $category));
        $this->assertSame('opini', $category->slug);
    }

    public function test_wartawan_cannot_access_category_index(): void
    {
        $wartawan = User::factory()->create([
            'role' => 'wartawan',
        ]);

        $this->actingAs($wartawan)
            ->get(route('admin.categories.index'))
            ->assertForbidden();
    }
}
