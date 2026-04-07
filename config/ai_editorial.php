<?php

return [
    'provider' => env('AI_EDITORIAL_PROVIDER', 'gemini'),
    'model' => env('AI_EDITORIAL_MODEL', 'gemini-2.5-flash-lite'),
    'enabled' => env('AI_EDITORIAL_ENABLED', false),
    'ingest_enabled' => env('AI_EDITORIAL_INGEST_ENABLED', false),
    'api_key' => env('AI_EDITORIAL_API_KEY'),
    'author_email' => env('AI_EDITORIAL_AUTHOR_EMAIL'),

    'limits' => [
        'drafts_per_day' => (int) env('AI_EDITORIAL_MAX_DRAFTS_PER_DAY', 10),
        'calls_per_day' => (int) env('AI_EDITORIAL_MAX_CALLS_PER_DAY', 100),
    ],

    'validation' => [
        'min_title_length' => (int) env('AI_EDITORIAL_MIN_TITLE_LENGTH', 18),
        'min_excerpt_length' => (int) env('AI_EDITORIAL_MIN_EXCERPT_LENGTH', 40),
        'max_source_age_hours' => (int) env('AI_EDITORIAL_MAX_SOURCE_AGE_HOURS', 72),
        'allowed_schemes' => ['http', 'https'],
    ],

    'ingest' => [
        'timeout_seconds' => (int) env('AI_EDITORIAL_INGEST_TIMEOUT', 20),
        'default_limit' => (int) env('AI_EDITORIAL_INGEST_LIMIT', 10),
    ],

    'generation' => [
        'timeout_seconds' => (int) env('AI_EDITORIAL_GENERATION_TIMEOUT', 40),
        'default_limit' => (int) env('AI_EDITORIAL_GENERATION_LIMIT', 10),
        'endpoint' => env('AI_EDITORIAL_GENERATION_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models'),
    ],

    'regions' => [
        'priority' => [
            'kotabaru',
            'tanah-bumbu',
            'kalimantan-selatan',
        ],
    ],

    'sources' => [
        [
            'code' => 'kotabaru_media_center',
            'name' => 'Media Center Kotabaru',
            'base_url' => 'https://mediacenter.kotabarukab.go.id/',
            'region' => 'kotabaru',
            'type' => 'government_media_center',
            'feed_url' => 'https://mediacenter.kotabarukab.go.id/feed/',
            'active' => true,
        ],
        [
            'code' => 'tanah_bumbu_media_center',
            'name' => 'Media Center Tanah Bumbu',
            'base_url' => 'https://mc.tanahbumbukab.go.id/',
            'region' => 'tanah-bumbu',
            'type' => 'government_media_center',
            'feed_url' => 'https://mc.tanahbumbukab.go.id/feed/',
            'active' => true,
        ],
        [
            'code' => 'kalsel_prov_news',
            'name' => 'Pemprov Kalimantan Selatan',
            'base_url' => 'https://kalselprov.go.id/berita/',
            'region' => 'kalimantan-selatan',
            'type' => 'government_portal',
            'feed_url' => 'https://kalselprov.go.id/berita/feed/',
            'active' => true,
        ],
        [
            'code' => 'antara_kalsel',
            'name' => 'ANTARA Kalsel',
            'base_url' => 'https://kalsel.antaranews.com/',
            'region' => 'kalimantan-selatan',
            'type' => 'news_agency',
            'feed_url' => 'https://kalsel.antaranews.com/rss',
            'active' => true,
        ],
    ],
];
