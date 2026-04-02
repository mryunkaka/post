<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_site_settings(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.settings.update'), [
            'site_name' => 'Portal Uji',
            'site_description' => 'Deskripsi uji.',
            'site_tagline' => 'Tagline uji.',
            'contact_email' => 'redaksi@example.com',
            'feature_amp_enabled' => true,
            'feature_ai_enabled' => false,
            'feature_comment_enabled' => true,
        ]);

        $response->assertRedirect(route('admin.settings.edit'));

        $this->assertSame('Portal Uji', Setting::query()->where('key', 'site_name')->value('value'));
        $this->assertSame('1', Setting::query()->where('key', 'feature_amp_enabled')->value('value'));
        $this->assertSame('1', Setting::query()->where('key', 'feature_comment_enabled')->value('value'));
    }

    public function test_editor_cannot_access_setting_page(): void
    {
        $editor = User::factory()->create([
            'role' => 'editor',
        ]);

        $this->actingAs($editor)
            ->get(route('admin.settings.edit'))
            ->assertForbidden();
    }
}
