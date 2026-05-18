<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PageWidget extends Model
{
    public const TARGET_BUILTIN = 'builtin';
    public const TARGET_STATIC_PAGE = 'static_page';

    public const AREA_PRE_FOOTER = 'pre_footer';
    public const AREA_HOME_HERO = 'home_hero';

    public const COLUMN_LEFT = 'left';
    public const COLUMN_RIGHT = 'right';

    public const TYPE_STATIC_IMAGE = 'static_image';
    public const TYPE_LINK_BANNER = 'link_banner';
    public const TYPE_HTML = 'html';
    public const TYPE_EMBED = 'embed';
    public const TYPE_TEXT_CTA = 'text_cta';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'static_page_id',
        'display_area',
        'title',
        'target_type',
        'target_path',
        'column',
        'widget_type',
        'status',
        'sort_order',
        'image_path',
        'image_alt',
        'link_url',
        'link_target',
        'html_content',
        'embed_url',
        'text_body',
        'cta_label',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public static function builtinTargets(): array
    {
        return [
            ['path' => '/', 'label' => 'Beranda'],
            ['path' => '/profil', 'label' => 'Profil'],
            ['path' => '/berita', 'label' => 'Berita'],
            ['path' => '/pengumuman', 'label' => 'Pengumuman'],
            ['path' => '/layanan', 'label' => 'Layanan'],
            ['path' => '/galeri', 'label' => 'Galeri'],
            ['path' => '/download', 'label' => 'Download'],
            ['path' => '/kontak', 'label' => 'Kontak'],
        ];
    }

    public static function columns(): array
    {
        return [
            self::COLUMN_LEFT => 'Kiri',
            self::COLUMN_RIGHT => 'Kanan',
        ];
    }

    public static function areas(): array
    {
        return [
            self::AREA_PRE_FOOTER => 'Pre-footer halaman',
            self::AREA_HOME_HERO => 'Hero beranda kanan',
        ];
    }

    public static function types(): array
    {
        return [
            self::TYPE_STATIC_IMAGE => 'Static Image',
            self::TYPE_LINK_BANNER => 'Link Banner',
            self::TYPE_HTML => 'HTML Terbatas',
            self::TYPE_EMBED => 'Embed',
            self::TYPE_TEXT_CTA => 'Text CTA',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PUBLISHED => 'Published',
        ];
    }

    public function staticPage(): BelongsTo
    {
        return $this->belongsTo(StaticPage::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('target_type')
            ->orderBy('target_path')
            ->orderBy('static_page_id')
            ->orderBy('display_area')
            ->orderBy('column')
            ->orderBy('sort_order')
            ->orderBy('title');
    }

    public function targetPath(): ?string
    {
        if ($this->target_type === self::TARGET_STATIC_PAGE) {
            return $this->staticPage?->path;
        }

        return $this->target_path ?: '/';
    }

    public function targetLabel(): string
    {
        if ($this->target_type === self::TARGET_STATIC_PAGE) {
            return $this->staticPage
                ? "{$this->staticPage->title} ({$this->staticPage->path})"
                : 'Halaman statis tidak tersedia';
        }

        $target = collect(self::builtinTargets())
            ->firstWhere('path', $this->target_path ?: '/');

        return $target
            ? "{$target['label']} ({$target['path']})"
            : ($this->target_path ?: '/');
    }

    public function imageUrl(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        if (Str::startsWith($this->image_path, ['http://', 'https://', '/'])) {
            return $this->image_path;
        }

        return '/storage/'.ltrim($this->image_path, '/');
    }

    public function publicPayload(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->widget_type,
            'title' => $this->title,
            'image_url' => $this->imageUrl(),
            'image_alt' => $this->image_alt ?: $this->title,
            'link_url' => $this->link_url,
            'link_target' => $this->link_target ?: '_self',
            'html_content' => $this->html_content,
            'embed_url' => $this->embed_url,
            'text_body' => $this->text_body,
            'cta_label' => $this->cta_label,
        ];
    }
}
