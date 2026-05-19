<?php

namespace App\Services;

use App\Models\DepartmentNewsSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DepartmentNewsService
{
    public function publicPayload(?DepartmentNewsSetting $setting = null): array
    {
        $setting ??= DepartmentNewsSetting::current();

        return [
            'enabled' => (bool) $setting->is_enabled,
            'title' => $setting->title ?: DepartmentNewsSetting::defaults()['title'],
            'description' => $setting->description ?: DepartmentNewsSetting::defaults()['description'],
            'items' => $setting->is_enabled ? $this->items($setting) : [],
        ];
    }

    public function items(DepartmentNewsSetting $setting, bool $forceRefresh = false): array
    {
        $limit = $setting->normalizedLimit();
        $freshKey = $this->freshCacheKey($limit);
        $lastSuccessfulKey = $this->lastSuccessfulCacheKey($limit);

        if (! $forceRefresh && Cache::has($freshKey)) {
            return Cache::get($freshKey, []);
        }

        $items = $this->fetchItems($limit);

        if ($items !== null) {
            Cache::put($freshKey, $items, now()->addMinutes($setting->normalizedCacheTtl()));
            Cache::forever($lastSuccessfulKey, $items);

            return $items;
        }

        return Cache::get($lastSuccessfulKey, []);
    }

    public function refresh(DepartmentNewsSetting $setting): array
    {
        Cache::forget($this->freshCacheKey($setting->normalizedLimit()));

        return $this->items($setting, true);
    }

    public function clearAll(): void
    {
        for ($limit = 1; $limit <= 20; $limit++) {
            Cache::forget($this->freshCacheKey($limit));
            Cache::forget($this->lastSuccessfulCacheKey($limit));
        }
    }

    private function fetchItems(int $limit): ?array
    {
        try {
            $response = Http::timeout((int) config('department_news.timeout', 8))
                ->retry((int) config('department_news.retry_times', 1), (int) config('department_news.retry_sleep_ms', 200))
                ->get((string) config('department_news.api_url'), [
                    'per_page' => $limit,
                ]);
        } catch (\Throwable) {
            return null;
        }

        if (! $response->ok()) {
            return null;
        }

        $rows = $response->json('data');

        if (! is_array($rows)) {
            return null;
        }

        return collect($rows)
            ->map(fn (mixed $row): ?array => is_array($row) ? $this->normalizeItem($row) : null)
            ->filter()
            ->sortByDesc('timestamp')
            ->take($limit)
            ->map(fn (array $item): array => collect($item)->except('timestamp')->all())
            ->values()
            ->all();
    }

    private function normalizeItem(array $row): ?array
    {
        $website = Str::lower(trim((string) ($row['website'] ?? '')));
        $slug = trim((string) ($row['slug'] ?? ''));

        if (! preg_match('/^[a-z0-9-]+$/', $website) || ! preg_match('/^[a-z0-9-]+$/', $slug)) {
            return null;
        }

        $title = trim(strip_tags((string) ($row['judul'] ?? '')));

        if ($title === '') {
            return null;
        }

        $dateValue = $row['created_at'] ?? $row['tanggalBerita'] ?? null;
        $date = $this->parseDate($dateValue);
        $image = trim((string) ($row['gambar'] ?? ''));

        return [
            'source' => $website,
            'title' => $title,
            'date' => $date['label'],
            'raw_date' => $date['raw'],
            'image_url' => $image !== ''
                ? "https://{$website}.langkatkab.go.id/storage/berita/".$this->safePathSegment($image)
                : null,
            'link_url' => "https://{$website}.langkatkab.go.id/berita/{$slug}",
            'slug' => $slug,
            'timestamp' => $date['timestamp'],
        ];
    }

    private function parseDate(mixed $value): array
    {
        try {
            $date = $value ? Carbon::parse((string) $value) : null;
        } catch (\Throwable) {
            $date = null;
        }

        if (! $date) {
            return [
                'label' => trim((string) $value),
                'raw' => trim((string) $value),
                'timestamp' => 0,
            ];
        }

        return [
            'label' => $date->locale('id')->translatedFormat('d F Y'),
            'raw' => $date->toIso8601String(),
            'timestamp' => $date->getTimestamp(),
        ];
    }

    private function safePathSegment(string $value): string
    {
        return rawurlencode(basename($value));
    }

    private function freshCacheKey(int $limit): string
    {
        return "department_news.items.{$limit}";
    }

    private function lastSuccessfulCacheKey(int $limit): string
    {
        return "department_news.items.last_successful.{$limit}";
    }
}
