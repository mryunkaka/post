<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SlugService
{
    public function generateUniqueSlug(string $value, string $modelClass, string $column = 'slug', ?Model $ignore = null): string
    {
        $baseSlug = Str::slug($value);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'artikel';

        $slug = $baseSlug;
        $counter = 2;

        while ($this->slugExists($slug, $modelClass, $column, $ignore)) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    protected function slugExists(string $slug, string $modelClass, string $column, ?Model $ignore): bool
    {
        $query = $modelClass::query()->where($column, $slug);

        if ($ignore !== null && $ignore->exists) {
            $query->whereKeyNot($ignore->getKey());
        }

        return $query->exists();
    }
}
