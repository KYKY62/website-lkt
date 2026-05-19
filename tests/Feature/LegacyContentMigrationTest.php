<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\DownloadDocument;
use App\Models\NewsArticle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class LegacyContentMigrationTest extends TestCase
{
    use RefreshDatabase;

    private string $legacyDatabasePath;

    protected function setUp(): void
    {
        parent::setUp();

        DB::purge('legacy_testing');

        $this->legacyDatabasePath = storage_path('framework/testing/legacy-'.Str::uuid().'.sqlite');

        if (file_exists($this->legacyDatabasePath)) {
            unlink($this->legacyDatabasePath);
        }

        touch($this->legacyDatabasePath);

        Config::set('database.connections.legacy_testing', [
            'driver' => 'sqlite',
            'database' => $this->legacyDatabasePath,
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);
        Config::set('legacy.connection', 'legacy_testing');
        Config::set('legacy.base_url', 'https://legacy.test');

        $this->createLegacySchema();
        $this->seedLegacyContent();
    }

    protected function tearDown(): void
    {
        DB::purge('legacy_testing');

        if (isset($this->legacyDatabasePath) && file_exists($this->legacyDatabasePath)) {
            @unlink($this->legacyDatabasePath);
        }

        parent::tearDown();
    }

    public function test_legacy_migration_dry_run_reports_target_counts(): void
    {
        $this
            ->artisan('legacy:migrate-content', ['--dry-run' => true])
            ->expectsOutput('news target: 2')
            ->expectsOutput('announcements target: 1')
            ->expectsOutput('downloads target: 1')
            ->assertSuccessful();

        $this->assertDatabaseCount('news_articles', 0);
        $this->assertDatabaseCount('announcements', 0);
        $this->assertDatabaseCount('download_documents', 0);
    }

    public function test_legacy_migration_imports_content_files_and_is_idempotent(): void
    {
        Storage::fake('public');
        Http::fake([
            'https://legacy.test/*' => Http::response('%PDF-or-image-content', 200, ['Content-Type' => 'application/pdf']),
        ]);

        $this->artisan('legacy:migrate-content')->assertSuccessful();
        $this->artisan('legacy:migrate-content')->assertSuccessful();

        $this->assertDatabaseCount('news_articles', 2);
        $this->assertDatabaseCount('announcements', 1);
        $this->assertDatabaseCount('download_documents', 1);

        $news = NewsArticle::query()->where('legacy_id', 5)->firstOrFail();
        $this->assertSame('judul-sama', $news->slug);
        $this->assertStringNotContainsString('<script', $news->content);
        $this->assertStringNotContainsString('onclick', $news->content);
        $this->assertStringNotContainsString('javascript:', $news->content);
        $this->assertNotEmpty($news->image_urls);
        Storage::disk('public')->assertExists($news->cover_image_url);

        $duplicate = NewsArticle::query()->where('legacy_id', 6)->firstOrFail();
        $this->assertSame('judul-sama-6', $duplicate->slug);

        $announcement = Announcement::query()->where('legacy_id', 10)->firstOrFail();
        $this->assertSame('Umum', $announcement->category);
        $this->assertNotNull($announcement->file_path);
        Storage::disk('public')->assertExists($announcement->file_path);

        $download = DownloadDocument::query()->where('legacy_id', 20)->firstOrFail();
        $this->assertSame('Dokumen', $download->category);
        $this->assertNotNull($download->file_path);
        Storage::disk('public')->assertExists($download->file_path);
    }

    public function test_legacy_redirects_resolve_to_new_content_and_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('legacy/announcements/10/file.pdf', '%PDF-announcement');
        Storage::disk('public')->put('legacy/downloads/20/file.pdf', '%PDF-download');

        NewsArticle::query()->create([
            'legacy_id' => 5,
            'title' => 'Judul Lama',
            'slug' => 'judul-lama',
            'category' => 'Berita',
            'excerpt' => 'Ringkasan',
            'content' => '<p>Isi</p>',
            'status' => 'published',
            'published_at' => now(),
        ]);

        Announcement::query()->create([
            'legacy_id' => 10,
            'title' => 'Pengumuman Lama',
            'slug' => 'pengumuman-lama',
            'category' => 'Umum',
            'content' => '<p>Isi</p>',
            'file_path' => 'legacy/announcements/10/file.pdf',
            'file_name' => 'file.pdf',
            'status' => Announcement::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        DownloadDocument::query()->create([
            'legacy_id' => 20,
            'title' => 'Download Lama',
            'slug' => 'download-lama',
            'category' => 'Dokumen',
            'file_path' => 'legacy/downloads/20/file.pdf',
            'file_name' => 'file.pdf',
            'status' => DownloadDocument::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $this->get('/berita/5/slug-lama')->assertRedirect('/berita/judul-lama');
        $this->get('/pengumuman/detil/10/slug-lama')->assertRedirect('/pengumuman/pengumuman-lama');
        $this->get('/pengumuman/get/10/anything.pdf')->assertOk();
        $this->get('/download/get/20/anything.pdf')->assertOk();
    }

    private function createLegacySchema(): void
    {
        $schema = DB::connection('legacy_testing')->getSchemaBuilder();

        $schema->create('lkt_berita_kategori', function ($table): void {
            $table->integer('id_kat');
            $table->string('nama');
        });

        $schema->create('lkt_berita', function ($table): void {
            $table->integer('id');
            $table->integer('id_cat');
            $table->string('judul');
            $table->text('content');
            $table->string('img');
            $table->dateTime('terbit');
            $table->dateTime('created');
            $table->integer('trash');
            $table->integer('status_terbit');
            $table->string('publisher');
        });

        $schema->create('lkt_pengumuman_kategori', function ($table): void {
            $table->integer('id_cat');
            $table->string('nama');
        });

        $schema->create('lkt_pengumuman', function ($table): void {
            $table->integer('id');
            $table->integer('id_kat');
            $table->string('judul');
            $table->text('content');
            $table->string('file');
            $table->integer('total_dw');
            $table->integer('terbit');
            $table->integer('trash');
            $table->dateTime('tanggal');
            $table->string('creator');
        });

        $schema->create('lkt_download_kategori', function ($table): void {
            $table->integer('id_kat');
            $table->string('judul');
        });

        $schema->create('lkt_download', function ($table): void {
            $table->integer('id');
            $table->integer('id_cat');
            $table->string('judul_file');
            $table->string('nama_file');
            $table->dateTime('tanggal');
            $table->text('deskripsi');
            $table->integer('total_dw');
            $table->integer('trash');
            $table->integer('status');
        });
    }

    private function seedLegacyContent(): void
    {
        $legacy = DB::connection('legacy_testing');

        $legacy->table('lkt_berita_kategori')->insert([
            ['id_kat' => 1, 'nama' => 'Berita Langkat'],
        ]);

        $legacy->table('lkt_berita')->insert([
            [
                'id' => 5,
                'id_cat' => 1,
                'judul' => 'Judul Sama',
                'content' => '<script>alert(1)</script><p onclick="bad()">Isi <a href="javascript:alert(1)">link</a></p><img src="/aset/img_berita/inline.jpg">',
                'img' => 'cover-one.jpg',
                'terbit' => '2026-05-01 10:00:00',
                'created' => '2026-05-01 09:00:00',
                'trash' => 0,
                'status_terbit' => 1,
                'publisher' => 'rizka',
            ],
            [
                'id' => 6,
                'id_cat' => 1,
                'judul' => 'Judul Sama',
                'content' => '<p>Isi kedua</p>',
                'img' => 'cover-two.jpg',
                'terbit' => '2026-05-02 10:00:00',
                'created' => '2026-05-02 09:00:00',
                'trash' => 0,
                'status_terbit' => 1,
                'publisher' => 'rizka',
            ],
            [
                'id' => 7,
                'id_cat' => 1,
                'judul' => 'Draft Lama',
                'content' => '<p>Draft</p>',
                'img' => '',
                'terbit' => '2026-05-02 10:00:00',
                'created' => '2026-05-02 09:00:00',
                'trash' => 0,
                'status_terbit' => 0,
                'publisher' => 'rizka',
            ],
        ]);

        $legacy->table('lkt_pengumuman_kategori')->insert([
            ['id_cat' => 1, 'nama' => 'Umum'],
        ]);

        $legacy->table('lkt_pengumuman')->insert([
            [
                'id' => 10,
                'id_kat' => 1,
                'judul' => 'Pengumuman Lama',
                'content' => '<p>Isi pengumuman</p>',
                'file' => 'pengumuman.pdf',
                'total_dw' => 3,
                'terbit' => 1,
                'trash' => 0,
                'tanggal' => '2026-04-01 08:00:00',
                'creator' => 'rizka',
            ],
        ]);

        $legacy->table('lkt_download_kategori')->insert([
            ['id_kat' => 2, 'judul' => '<script>alert(1)</script>'],
        ]);

        $legacy->table('lkt_download')->insert([
            [
                'id' => 20,
                'id_cat' => 2,
                'judul_file' => 'Dokumen Lama',
                'nama_file' => 'dokumen.pdf',
                'tanggal' => '2026-03-01 08:00:00',
                'deskripsi' => 'Deskripsi dokumen',
                'total_dw' => 4,
                'trash' => 0,
                'status' => 1,
            ],
        ]);
    }
}
