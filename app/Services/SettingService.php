<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    public function __construct(
        protected FrontCacheService $frontCacheService,
    ) {}

    public function getGroup(string $group): Collection
    {
        return $this->autoloaded()
            ->where('group', $group)
            ->keyBy('key');
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function updateGroup(string $group, array $values, bool $autoload = true): void
    {
        foreach ($values as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'group' => $group,
                    'value' => $this->normalizeValue($value),
                    'autoload' => $autoload,
                ]
            );
        }

        $this->frontCacheService->flushSettingRelatedCaches();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $setting = $this->autoloaded()->firstWhere('key', $key);

        return $setting?->value ?? $default;
    }

    protected function autoloaded(): Collection
    {
        $cacheKey = $this->frontCacheService->key('settings.autoload');
        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return collect($cached)->map(
                fn (array $row) => (new Setting)->forceFill($row)
            );
        }

        if ($cached !== null) {
            Cache::forget($cacheKey);
        }

        $payload = Setting::query()
            ->where('autoload', true)
            ->orderBy('group')
            ->orderBy('key')
            ->get(['group', 'key', 'value', 'autoload'])
            ->map(fn (Setting $setting) => [
                'group' => $setting->group,
                'key' => $setting->key,
                'value' => $setting->value,
                'autoload' => (bool) $setting->autoload,
            ])
            ->values()
            ->all();

        Cache::put($cacheKey, $payload, FrontCacheService::SETTINGS_AUTOLOAD_TTL);

        return collect($payload)->map(
            fn (array $row) => (new Setting)->forceFill($row)
        );
    }

    protected function normalizeValue(mixed $value): ?string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
