<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use App\Models\DownloadDocument;
use App\Models\NewsArticle;
use App\Support\ContentSanitizer;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MigrateLegacyContent extends Command
{
    protected $signature = 'legacy:migrate-content
        {--only=news,announcements,downloads : Comma-separated content types to migrate}
        {--dry-run : Show target counts without writing data or files}';

    protected $description = 'Migrate published legacy Langkat news, announcements, and downloads into the new CMS.';

    public function __construct(private readonly ContentSanitizer $sanitizer)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $types = $this->selectedTypes();

        if ($types === null) {
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $legacy = DB::connection(config('legacy.connection', 'legacy'));

        foreach ($types as $type) {
            $count = $this->legacyCount($legacy, $type);
            $this->line("{$type} target: {$count}");

            if ($dryRun) {
                continue;
            }

            match ($type) {
                'news' => $this->migrateNews($legacy),
                'announcements' => $this->migrateAnnouncements($legacy),
                'downloads' => $this->migrateDownloads($legacy),
            };
        }

        $this->info($dryRun ? 'Dry-run selesai.' : 'Migrasi konten legacy selesai.');

        return self::SUCCESS;
    }

    private function selectedTypes(): ?array
    {
        $allowed = ['news', 'announcements', 'downloads'];
        $types = collect(explode(',', (string) $this->option('only')))
            ->map(fn (string $type): string => trim($type))
            ->filter()
            ->values()
            ->all();

        $invalid = array_diff($types, $allowed);

        if ($types === [] || $invalid !== []) {
            $this->error('Opsi --only harus berisi salah satu dari: '.implode(',', $allowed));

            return null;
        }

        return $types;
    }

    private function legacyCount(ConnectionInterface $legacy, string $type): int
    {
        return match ($type) {
            'news' => (int) $legacy->table('lkt_berita')->where('trash', 0)->where('status_terbit', 1)->count(),
            'announcements' => (int) $legacy->table('lkt_pengumuman')->where('trash', 0)->where('terbit', 1)->count(),
            'downloads' => (int) $legacy->table('lkt_download')->where('trash', 0)->where('status', 1)->count(),
        };
    }

    private function migrateNews(ConnectionInterface $legacy): void
    {
        $rows = $legacy->table('lkt_berita as b')
            ->leftJoin('lkt_berita_kategori as c', 'b.id_cat', '=', 'c.id_kat')
            ->select('b.id', 'b.judul', 'b.content', 'b.img', 'b.terbit', 'b.created', 'b.publisher', 'c.nama as category')
            ->where('b.trash', 0)
            ->where('b.status_terbit', 1)
            ->orderBy('b.id')
            ->get();

        $bar = $this->output->createProgressBar($rows->count());
        $bar->start();

        foreach ($rows as $row) {
            $existing = NewsArticle::query()->where('legacy_id', $row->id)->first();
            $content = $this->sanitizer->html($row->content);
            $images = $this->resolveNewsImages($row, $existing);
            $slug = $existing?->slug ?: $this->uniqueSlug($row->judul, NewsArticle::class, null, (int) $row->id, 'berita');

            NewsArticle::query()->updateOrCreate(
                ['legacy_id' => $row->id],
                [
                    'title' => $this->sanitizer->title($row->judul, 'Berita'),
                    'slug' => $slug,
                    'category' => $this->sanitizer->title($row->category, 'Berita Langkat', 120),
                    'excerpt' => $this->excerpt($row->content, $row->judul),
                    'content' => $content,
                    'cover_image_url' => $images[0] ?? null,
                    'image_urls' => $images,
                    'status' => 'published',
                    'published_at' => $this->dateOrNow($row->terbit ?: $row->created),
                    'published_by' => $existing?->published_by,
                    'legacy_author' => $this->sanitizer->plain($row->publisher, '', 120) ?: null,
                ]
            );

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function migrateAnnouncements(ConnectionInterface $legacy): void
    {
        $rows = $legacy->table('lkt_pengumuman as p')
            ->leftJoin('lkt_pengumuman_kategori as c', 'p.id_kat', '=', 'c.id_cat')
            ->select('p.id', 'p.judul', 'p.content', 'p.file', 'p.total_dw', 'p.tanggal', 'p.creator', 'c.nama as category')
            ->where('p.trash', 0)
            ->where('p.terbit', 1)
            ->orderBy('p.id')
            ->get();

        $bar = $this->output->createProgressBar($rows->count());
        $bar->start();

        foreach ($rows as $row) {
            $existing = Announcement::query()->where('legacy_id', $row->id)->first();
            $file = $this->resolveLegacyFile(
                $existing?->file_path,
                $this->legacyUrl("/pengumuman/get/{$row->id}/x"),
                "legacy/announcements/{$row->id}",
                $row->file ?: "pengumuman-{$row->id}"
            );

            Announcement::query()->updateOrCreate(
                ['legacy_id' => $row->id],
                [
                    'title' => $this->sanitizer->title($row->judul, 'Pengumuman'),
                    'slug' => $existing?->slug ?: $this->uniqueSlug($row->judul, Announcement::class, null, (int) $row->id, 'pengumuman'),
                    'category' => $this->sanitizer->title($row->category, 'Umum', 160),
                    'content' => $this->sanitizer->html($row->content),
                    'file_path' => $file['path'] ?? null,
                    'file_name' => $file['name'] ?? ($row->file ?: null),
                    'mime_type' => $file['mime'] ?? $existing?->mime_type,
                    'file_size' => $file['size'] ?? $existing?->file_size,
                    'download_count' => (int) $row->total_dw,
                    'status' => Announcement::STATUS_PUBLISHED,
                    'published_at' => $this->dateOrNow($row->tanggal),
                    'published_by' => $existing?->published_by,
                    'legacy_author' => $this->sanitizer->plain($row->creator, '', 120) ?: null,
                ]
            );

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function migrateDownloads(ConnectionInterface $legacy): void
    {
        $rows = $legacy->table('lkt_download as d')
            ->leftJoin('lkt_download_kategori as c', 'd.id_cat', '=', 'c.id_kat')
            ->select('d.id', 'd.judul_file', 'd.nama_file', 'd.tanggal', 'd.deskripsi', 'd.total_dw', 'c.judul as category')
            ->where('d.trash', 0)
            ->where('d.status', 1)
            ->orderBy('d.id')
            ->get();

        $bar = $this->output->createProgressBar($rows->count());
        $bar->start();

        foreach ($rows as $row) {
            $existing = DownloadDocument::query()->where('legacy_id', $row->id)->first();
            $file = $this->resolveLegacyFile(
                $existing?->file_path,
                $this->legacyUrl("/download/get/{$row->id}/x"),
                "legacy/downloads/{$row->id}",
                $row->nama_file ?: "download-{$row->id}"
            );

            DownloadDocument::query()->updateOrCreate(
                ['legacy_id' => $row->id],
                [
                    'title' => $this->sanitizer->title($row->judul_file, 'Dokumen'),
                    'slug' => $existing?->slug ?: $this->uniqueSlug($row->judul_file, DownloadDocument::class, null, (int) $row->id, 'dokumen'),
                    'category' => $this->sanitizer->title($row->category, 'Dokumen', 160),
                    'description' => $this->sanitizer->plain($row->deskripsi, '', 2000),
                    'file_path' => $file['path'] ?? null,
                    'file_name' => $file['name'] ?? ($row->nama_file ?: null),
                    'mime_type' => $file['mime'] ?? $existing?->mime_type,
                    'file_size' => $file['size'] ?? $existing?->file_size,
                    'download_count' => (int) $row->total_dw,
                    'status' => DownloadDocument::STATUS_PUBLISHED,
                    'published_at' => $this->dateOrNow($row->tanggal),
                    'published_by' => $existing?->published_by,
                ]
            );

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function resolveNewsImages(object $row, ?NewsArticle $existing): array
    {
        if ($existing?->image_urls && $existing->galleryImages() !== []) {
            return $existing->image_urls;
        }

        $images = [];

        if (trim((string) $row->img) !== '') {
            $file = $this->resolveLegacyFile(
                null,
                $this->legacyUrl('/aset/img_berita/'.$this->urlPathSegment($row->img)),
                "legacy/news/{$row->id}",
                $row->img
            );

            if ($file['path'] ?? null) {
                $images[] = $file['path'];
            }
        }

        foreach ($this->extractImageSources($row->content) as $index => $imageUrl) {
            $file = $this->resolveLegacyFile(
                null,
                $this->absoluteLegacyUrl($imageUrl),
                "legacy/news/{$row->id}",
                basename(parse_url($imageUrl, PHP_URL_PATH) ?: "inline-{$index}.jpg")
            );

            if ($file['path'] ?? null) {
                $images[] = $file['path'];
            }
        }

        return array_values(array_unique($images));
    }

    private function resolveLegacyFile(?string $existingPath, string $url, string $directory, string $fileName): ?array
    {
        if ($existingPath && Storage::disk('public')->exists($existingPath)) {
            return [
                'path' => $existingPath,
                'name' => basename($existingPath),
                'mime' => Storage::disk('public')->mimeType($existingPath),
                'size' => Storage::disk('public')->size($existingPath),
            ];
        }

        try {
            $response = Http::timeout(30)->retry(2, 200)->get($url);
        } catch (\Throwable $exception) {
            $this->warn("Gagal mengambil file legacy: {$url}");

            return null;
        }

        if (! $response->ok() || $response->body() === '') {
            $this->warn("File legacy tidak tersedia: {$url}");

            return null;
        }

        $mime = (string) $response->header('Content-Type');

        if (str_contains(strtolower($mime), 'text/html')) {
            $this->warn("File legacy mengembalikan HTML, dilewati: {$url}");

            return null;
        }

        $safeName = $this->safeFilename($fileName, $mime);
        $path = trim($directory, '/').'/'.$safeName;

        Storage::disk('public')->put($path, $response->body());

        return [
            'path' => $path,
            'name' => $safeName,
            'mime' => $mime ?: Storage::disk('public')->mimeType($path),
            'size' => Storage::disk('public')->size($path),
        ];
    }

    private function extractImageSources(?string $html): array
    {
        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', (string) $html, $matches);

        return collect($matches[1] ?? [])
            ->map(fn (string $url): string => trim($url))
            ->filter()
            ->values()
            ->all();
    }

    private function uniqueSlug(?string $title, string $modelClass, ?int $ignoreId, int $legacyId, string $fallback): string
    {
        $base = Str::slug($title ?: $fallback) ?: $fallback;
        $slug = $base;

        if ($this->slugExists($modelClass, $slug, $ignoreId)) {
            $slug = "{$base}-{$legacyId}";
        }

        $counter = 2;

        while ($this->slugExists($modelClass, $slug, $ignoreId)) {
            $slug = "{$base}-{$legacyId}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $modelClass, string $slug, ?int $ignoreId): bool
    {
        return $modelClass::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    private function excerpt(?string $html, ?string $fallback): string
    {
        return $this->sanitizer->plain($html, $this->sanitizer->title($fallback, 'Berita'), 500);
    }

    private function dateOrNow(?string $date): Carbon
    {
        try {
            return $date ? Carbon::parse($date) : now();
        } catch (\Throwable) {
            return now();
        }
    }

    private function legacyUrl(string $path): string
    {
        return rtrim((string) config('legacy.base_url'), '/').'/'.ltrim($path, '/');
    }

    private function absoluteLegacyUrl(string $url): string
    {
        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        return $this->legacyUrl($url);
    }

    private function urlPathSegment(?string $value): string
    {
        return str_replace('%2F', '/', rawurlencode((string) $value));
    }

    private function safeFilename(string $fileName, string $mime): string
    {
        $base = basename($fileName);
        $base = preg_replace('/[^A-Za-z0-9._-]+/', '-', $base) ?: 'file';
        $base = trim($base, '.-');

        if (! str_contains($base, '.')) {
            $base .= match (strtolower(strtok($mime, ';') ?: '')) {
                'application/pdf' => '.pdf',
                'image/jpeg' => '.jpg',
                'image/png' => '.png',
                'image/webp' => '.webp',
                default => '.bin',
            };
        }

        return $base;
    }
}
