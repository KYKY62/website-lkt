<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentNewsSetting extends Model
{
    protected $fillable = [
        'is_enabled',
        'title',
        'description',
        'item_limit',
        'cache_ttl_minutes',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'item_limit' => 'integer',
            'cache_ttl_minutes' => 'integer',
        ];
    }

    public static function defaults(): array
    {
        return [
            'is_enabled' => true,
            'title' => 'Kabar Perangkat Daerah',
            'description' => 'Berita terbaru dari website perangkat daerah dan kecamatan di lingkungan Pemerintah Kabupaten Langkat.',
            'item_limit' => 7,
            'cache_ttl_minutes' => 10,
        ];
    }

    public static function current(): self
    {
        return self::query()->first() ?? self::query()->create(self::defaults());
    }

    public function normalizedLimit(): int
    {
        return max(1, min(20, (int) ($this->item_limit ?: self::defaults()['item_limit'])));
    }

    public function normalizedCacheTtl(): int
    {
        return max(1, min(1440, (int) ($this->cache_ttl_minutes ?: self::defaults()['cache_ttl_minutes'])));
    }
}
