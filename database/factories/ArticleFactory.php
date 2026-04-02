<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        $title = fake()->sentence(6);

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(10, 9999),
            'excerpt' => fake()->paragraph(),
            'content' => fake()->paragraphs(4, true),
            'featured_image' => null,
            'status' => 'draft',
            'review_notes' => null,
            'meta_title' => $title,
            'meta_description' => fake()->sentence(),
            'schema_type' => 'NewsArticle',
            'views_count' => 0,
            'is_featured' => false,
            'created_by_ai' => false,
            'published_at' => null,
            'archived_at' => null,
        ];
    }
}
