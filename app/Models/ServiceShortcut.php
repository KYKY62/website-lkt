<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ServiceShortcut extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'title',
        'organizer',
        'description',
        'logo_path',
        'link_url',
        'link_target',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PUBLISHED => 'Published',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('title');
    }

    public function logoUrl(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        if (Str::startsWith($this->logo_path, ['http://', 'https://', '/'])) {
            return $this->logo_path;
        }

        return '/storage/'.ltrim($this->logo_path, '/');
    }

    public function publicPayload(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'organizer' => $this->organizer,
            'description' => $this->description,
            'logo_url' => $this->logoUrl(),
            'link_url' => $this->link_url,
            'link_target' => $this->link_target ?: '_self',
        ];
    }
}
