<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsArticle extends Model
{
    protected $fillable = [
        'legacy_id',
        'title',
        'slug',
        'category',
        'excerpt',
        'content',
        'cover_image_url',
        'image_urls',
        'status',
        'published_at',
        'published_by',
        'legacy_author',
    ];

    protected function casts(): array
    {
        return [
            'legacy_id' => 'integer',
            'image_urls' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at');
    }

    public function galleryImages(): array
    {
        $images = collect($this->image_urls ?? [])
            ->map(fn (?string $path): string => trim((string) $path))
            ->filter()
            ->map(fn (string $path): string => $this->resolveImagePath($path))
            ->values()
            ->all();

        if ($images !== []) {
            return $images;
        }

        return $this->cover_image_url ? [$this->resolveImagePath($this->cover_image_url)] : [];
    }

    public function coverImage(): ?string
    {
        if (! $this->cover_image_url) {
            return null;
        }

        return $this->resolveImagePath($this->cover_image_url);
    }

    private function resolveImagePath(string $path): string
    {
        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        return '/storage/'.ltrim($path, '/');
    }
}
