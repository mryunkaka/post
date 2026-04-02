<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = now();

        $categories = [
            [
                'name' => 'Berita',
                'slug' => 'berita',
                'description' => 'Kanal berita utama.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Lokal',
                'slug' => 'lokal',
                'description' => 'Berita lokal dan daerah.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Nasional',
                'slug' => 'nasional',
                'description' => 'Berita nasional.',
                'sort_order' => 3,
            ],
            [
                'name' => 'Ekonomi',
                'slug' => 'ekonomi',
                'description' => 'Berita ekonomi dan bisnis.',
                'sort_order' => 4,
            ],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(
                ['slug' => $category['slug']],
                [
                    'parent_id' => null,
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'sort_order' => $category['sort_order'],
                    'is_active' => true,
                    'seo_title' => $category['name'],
                    'seo_description' => $category['description'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
