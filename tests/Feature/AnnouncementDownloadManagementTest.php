<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\DownloadDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnnouncementDownloadManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_create_announcement_with_uploaded_file(): void
    {
        Storage::fake('public');

        $editor = User::factory()->create([
            'role' => User::ROLE_NEWS_EDITOR,
        ]);

        $response = $this
            ->actingAs($editor)
            ->post('/admin/announcements', [
                'title' => 'Pengumuman Seleksi Terbuka',
                'category' => 'Umum',
                'content' => '<script>alert(1)</script><p>Isi pengumuman resmi.</p>',
                'file' => UploadedFile::fake()->create('pengumuman.pdf', 128, 'application/pdf'),
                'status' => Announcement::STATUS_PUBLISHED,
                'published_at' => '2026-05-19 10:00:00',
            ]);

        $announcement = Announcement::query()->firstOrFail();

        $response->assertRedirect(route('admin.announcements.edit', $announcement));
        $this->assertStringNotContainsString('<script', $announcement->content);
        $this->assertSame($editor->id, $announcement->published_by);
        Storage::disk('public')->assertExists($announcement->file_path);
    }

    public function test_editor_can_create_download_document_with_uploaded_file(): void
    {
        Storage::fake('public');

        $editor = User::factory()->create([
            'role' => User::ROLE_NEWS_EDITOR,
        ]);

        $response = $this
            ->actingAs($editor)
            ->post('/admin/downloads', [
                'title' => 'Dokumen Renstra',
                'category' => 'Dokumen',
                'description' => 'Dokumen resmi untuk publik.',
                'file' => UploadedFile::fake()->create('renstra.pdf', 128, 'application/pdf'),
                'status' => DownloadDocument::STATUS_PUBLISHED,
                'published_at' => '2026-05-19 10:00:00',
            ]);

        $download = DownloadDocument::query()->firstOrFail();

        $response->assertRedirect(route('admin.downloads.edit', $download));
        $this->assertSame($editor->id, $download->published_by);
        Storage::disk('public')->assertExists($download->file_path);
    }

    public function test_public_site_exposes_published_announcements_and_downloads(): void
    {
        Announcement::query()->create([
            'title' => 'Pengumuman Publik',
            'slug' => 'pengumuman-publik',
            'category' => 'Umum',
            'content' => '<p>Isi</p>',
            'status' => Announcement::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        DownloadDocument::query()->create([
            'title' => 'Dokumen Publik',
            'slug' => 'dokumen-publik',
            'category' => 'Dokumen',
            'status' => DownloadDocument::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $response = $this->get('/pengumuman');

        $response
            ->assertOk()
            ->assertSee('Pengumuman Publik')
            ->assertSee('Dokumen Publik');
    }
}
