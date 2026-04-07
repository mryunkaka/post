<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsCandidate extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'source_code',
        'source_name',
        'source_url',
        'source_url_hash',
        'source_published_at',
        'region',
        'title',
        'excerpt',
        'image_url',
        'facts_summary',
        'raw_payload',
        'status',
        'rejection_reason',
        'article_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'source_published_at' => 'datetime',
            'raw_payload' => 'array',
            'article_id' => 'integer',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
