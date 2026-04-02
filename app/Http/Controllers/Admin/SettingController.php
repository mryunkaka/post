<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSiteSettingRequest;
use App\Services\SettingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SettingController extends Controller
{
    public function __construct(
        protected SettingService $settingService,
    ) {}

    public function edit(): View
    {
        $general = $this->settingService->getGroup('general');
        $feature = $this->settingService->getGroup('feature');

        return view('admin.settings.edit', [
            'settings' => [
                'site_name' => $general->get('site_name')?->value ?? config('app.brand_name', config('app.name')),
                'site_description' => $general->get('site_description')?->value,
                'site_tagline' => $general->get('site_tagline')?->value,
                'contact_email' => $general->get('contact_email')?->value,
                'feature_amp_enabled' => $this->asBool($feature->get('feature_amp_enabled')?->value, false),
                'feature_ai_enabled' => $this->asBool($feature->get('feature_ai_enabled')?->value, false),
                'feature_comment_enabled' => $this->asBool($feature->get('feature_comment_enabled')?->value, false),
            ],
        ]);
    }

    public function update(UpdateSiteSettingRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->settingService->updateGroup('general', [
            'site_name' => $validated['site_name'],
            'site_description' => $validated['site_description'] ?? null,
            'site_tagline' => $validated['site_tagline'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
        ]);

        $this->settingService->updateGroup('feature', [
            'feature_amp_enabled' => (bool) ($validated['feature_amp_enabled'] ?? false),
            'feature_ai_enabled' => (bool) ($validated['feature_ai_enabled'] ?? false),
            'feature_comment_enabled' => (bool) ($validated['feature_comment_enabled'] ?? false),
        ]);

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Setting situs berhasil diperbarui.');
    }

    protected function asBool(mixed $value, bool $default): bool
    {
        if ($value === null) {
            return $default;
        }

        return in_array((string) $value, ['1', 'true', 'on'], true);
    }
}
