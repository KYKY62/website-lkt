<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiteMenu extends Model
{
    public const TYPE_PAGE = 'page';
    public const TYPE_LINK = 'link';
    public const TYPE_MODULE = 'module';

    protected $fillable = [
        'label',
        'parent_id',
        'page_id',
        'item_type',
        'url',
        'target',
        'module_key',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function itemTypes(): array
    {
        return [
            self::TYPE_PAGE,
            self::TYPE_LINK,
            self::TYPE_MODULE,
        ];
    }

    public static function moduleOptions(): array
    {
        return [
            ['key' => 'home', 'label' => 'Beranda', 'path' => '/'],
            ['key' => 'profile', 'label' => 'Profil', 'path' => '/profil'],
            ['key' => 'news', 'label' => 'Berita', 'path' => '/berita'],
            ['key' => 'announcements', 'label' => 'Pengumuman', 'path' => '/pengumuman'],
            ['key' => 'services', 'label' => 'Layanan', 'path' => '/layanan'],
            ['key' => 'gallery', 'label' => 'Galeri', 'path' => '/galeri'],
            ['key' => 'downloads', 'label' => 'Download', 'path' => '/download'],
            ['key' => 'contact', 'label' => 'Kontak', 'path' => '/kontak'],
        ];
    }

    public static function modulePath(string $key): ?string
    {
        return collect(self::moduleOptions())
            ->firstWhere('key', $key)['path'] ?? null;
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('label');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(StaticPage::class, 'page_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
