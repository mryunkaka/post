<?php

namespace Database\Factories;

use App\Models\NewsCandidate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<NewsCandidate>
 */
class NewsCandidateFactory extends Factory
{
    protected $model = NewsCandidate::class;

    public function definition(): array
    {
        $slug = fake()->slug();

        return [
            'source_code' => 'antara_kalsel',
            'source_name' => 'ANTARA Kalsel',
            'source_url' => $sourceUrl = 'https://example.test/berita/'.$slug,
            'source_url_hash' => hash('sha256', strtolower($sourceUrl)),
            'source_published_at' => now()->subHour(),
            'region' => 'kalimantan-selatan',
            'title' => Str::title(fake()->sentence(7)),
            'excerpt' => fake()->paragraph(),
            'image_url' => fake()->imageUrl(),
            'facts_summary' => fake()->paragraph(),
            'raw_payload' => ['title' => fake()->sentence(), 'slug' => $slug],
            'status' => 'pending',
            'rejection_reason' => null,
            'article_id' => null,
        ];
    }
}
