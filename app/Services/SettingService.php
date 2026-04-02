<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Collection;

class SettingService
{
    public function getGroup(string $group): Collection
    {
        return Setting::query()
            ->where('group', $group)
            ->orderBy('key')
            ->get()
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
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $setting = Setting::query()->where('key', $key)->first();

        return $setting?->value ?? $default;
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
