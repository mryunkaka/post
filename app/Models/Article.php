<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Route;

class Article extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'status',
        'review_notes',
        'meta_title',
        'meta_description',
        'schema_type',
        'views_count',
        'is_featured',
        'created_by_ai',
        'published_at',
        'archived_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'category_id' => 'integer',
            'views_count' => 'integer',
            'is_featured' => 'boolean',
            'created_by_ai' => 'boolean',
            'published_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'article_tags')->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNull('archived_at')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function publicUrl(): ?string
    {
        if ($this->slug === '' || $this->slug === null || ! Route::has('articles.show')) {
            return null;
        }

        return route('articles.show', $this->slug);
    }

    public function featuredImageUrl(): ?string
    {
        if ($this->featured_image === null || $this->featured_image === '') {
            return null;
        }

        if (Route::has('media.public')) {
            return route('media.public', ['path' => $this->featured_image]);
        }

        return null;
    }
}
