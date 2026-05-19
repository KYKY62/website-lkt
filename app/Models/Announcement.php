<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Announcement extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'legacy_id',
        'title',
        'slug',
        'category',
        'content',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'download_count',
        'status',
        'published_at',
        'published_by',
        'legacy_author',
    ];

    protected function casts(): array
    {
        return [
            'legacy_id' => 'integer',
            'file_size' => 'integer',
            'download_count' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PUBLISHED => 'Published',
        ];
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at');
    }

    public function fileUrl(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        return '/pengumuman/file/'.$this->slug;
    }

    public function storageUrl(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        if (Str::startsWith($this->file_path, ['http://', 'https://', '/'])) {
            return $this->file_path;
        }

        return '/storage/'.ltrim($this->file_path, '/');
    }

    public function publicPayload(): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'category' => $this->category,
            'type' => $this->category,
            'date' => $this->published_at?->locale('id')->translatedFormat('d F Y'),
            'content_html' => $this->content,
            'file_url' => $this->fileUrl(),
            'file_name' => $this->file_name,
            'file_size' => $this->file_size,
            'download_count' => $this->download_count,
            'editor_name' => $this->publishedBy?->name ?: $this->legacy_author,
        ];
    }
}
