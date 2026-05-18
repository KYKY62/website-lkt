<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaticPage extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'path',
        'excerpt',
        'content',
        'status',
    ];

    public static function reservedPaths(): array
    {
        return [
            '/',
            '/profil',
            '/berita',
            '/pengumuman',
            '/layanan',
            '/galeri',
            '/download',
            '/kontak',
            '/admin',
        ];
    }

    public function menus(): HasMany
    {
        return $this->hasMany(SiteMenu::class, 'page_id');
    }

    public function widgets(): HasMany
    {
        return $this->hasMany(PageWidget::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
