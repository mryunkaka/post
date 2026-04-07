<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Services\CategoryProvisionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryProvisionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_existing_category_when_name_matches(): void
    {
        $existingCategory = Category::factory()->create([
            'name' => 'Tanah Bumbu',
            'slug' => 'tanah-bumbu',
        ]);

        $resolvedCategory = app(CategoryProvisionService::class)->resolveOrCreate([
            'name' => ' tanah bumbu ',
            'description' => 'Kategori hasil AI.',
        ]);

        $this->assertTrue($resolvedCategory->is($existingCategory));
        $this->assertDatabaseCount('categories', 1);
    }

    public function test_it_returns_existing_category_when_slug_matches(): void
    {
        $existingCategory = Category::factory()->create([
            'name' => 'Kotabaru',
            'slug' => 'kotabaru',
        ]);

        $resolvedCategory = app(CategoryProvisionService::class)->resolveOrCreate([
            'name' => 'Kabupaten Kotabaru',
            'slug' => 'kotabaru',
        ]);

        $this->assertTrue($resolvedCategory->is($existingCategory));
        $this->assertDatabaseCount('categories', 1);
    }

    public function test_it_can_create_new_category_via_internal_service_without_admin_login(): void
    {
        $category = app(CategoryProvisionService::class)->resolveOrCreate([
            'name' => 'Infrastruktur Desa',
            'description' => 'Kategori otomatis dari pipeline AI editorial.',
        ]);

        $this->assertSame('Infrastruktur Desa', $category->name);
        $this->assertSame('infrastruktur-desa', $category->slug);
        $this->assertTrue($category->is_active);
        $this->assertSame(999, $category->sort_order);
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'slug' => 'infrastruktur-desa',
        ]);
    }
}
