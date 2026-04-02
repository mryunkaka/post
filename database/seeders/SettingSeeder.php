<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = now();

        $settings = [
            [
                'group' => 'general',
                'key' => 'site_name',
                'value' => 'TodakSiring',
            ],
            [
                'group' => 'general',
                'key' => 'site_description',
                'value' => 'Portal berita modern berbasis Laravel untuk workflow editorial, SEO, dan monetisasi.',
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'group' => $setting['group'],
                    'value' => $setting['value'],
                    'autoload' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
