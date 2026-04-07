<?php

return [
    'provider' => env('AI_EDITORIAL_PROVIDER', 'gemini'),
    'model' => env('AI_EDITORIAL_MODEL', 'gemini-2.5-flash-lite'),
    'enabled' => env('AI_EDITORIAL_ENABLED', false),

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
            'active' => true,
        ],
        [
            'code' => 'tanah_bumbu_media_center',
            'name' => 'Media Center Tanah Bumbu',
            'base_url' => 'https://mc.tanahbumbukab.go.id/',
            'region' => 'tanah-bumbu',
            'type' => 'government_media_center',
            'active' => true,
        ],
        [
            'code' => 'kalsel_prov_news',
            'name' => 'Pemprov Kalimantan Selatan',
            'base_url' => 'https://kalselprov.go.id/berita/',
            'region' => 'kalimantan-selatan',
            'type' => 'government_portal',
            'active' => true,
        ],
        [
            'code' => 'antara_kalsel',
            'name' => 'ANTARA Kalsel',
            'base_url' => 'https://kalsel.antaranews.com/',
            'region' => 'kalimantan-selatan',
            'type' => 'news_agency',
            'active' => true,
        ],
    ],
];
