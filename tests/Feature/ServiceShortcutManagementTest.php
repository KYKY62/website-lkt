<?php

namespace Tests\Feature;

use App\Models\ServiceShortcut;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServiceShortcutManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_create_service_shortcut_with_uploaded_logo(): void
    {
        Storage::fake('public');

        $editor = User::factory()->create([
            'role' => User::ROLE_NEWS_EDITOR,
        ]);

        $response = $this
            ->actingAs($editor)
            ->post('/admin/services', [
                'title' => 'Sistem Informasi Layanan Publik',
                'organizer' => 'Dinas Komunikasi dan Informatika',
                'description' => 'Kanal akses layanan publik perangkat daerah.',
                'logo' => UploadedFile::fake()->create('logo.png', 128, 'image/png'),
                'link_url' => 'https://layanan.langkatkab.go.id',
                'link_target' => '_blank',
                'status' => ServiceShortcut::STATUS_PUBLISHED,
                'sort_order' => 3,
            ]);

        $service = ServiceShortcut::query()->firstOrFail();

        $response->assertRedirect(route('admin.services.edit', $service));

        $this->assertDatabaseHas('service_shortcuts', [
            'title' => 'Sistem Informasi Layanan Publik',
            'organizer' => 'Dinas Komunikasi dan Informatika',
            'link_url' => 'https://layanan.langkatkab.go.id',
            'status' => ServiceShortcut::STATUS_PUBLISHED,
            'sort_order' => 3,
        ]);

        Storage::disk('public')->assertExists($service->logo_path);
    }

    public function test_public_site_exposes_only_published_service_shortcuts(): void
    {
        ServiceShortcut::query()->create([
            'title' => 'Layanan Published',
            'organizer' => 'Dinas Pelayanan',
            'description' => 'Layanan yang sudah tampil publik.',
            'link_url' => '/layanan-published',
            'link_target' => '_self',
            'status' => ServiceShortcut::STATUS_PUBLISHED,
            'sort_order' => 1,
        ]);

        ServiceShortcut::query()->create([
            'title' => 'Layanan Draft',
            'organizer' => 'Dinas Internal',
            'description' => 'Layanan yang belum boleh tampil publik.',
            'link_url' => '/layanan-draft',
            'link_target' => '_self',
            'status' => ServiceShortcut::STATUS_DRAFT,
            'sort_order' => 2,
        ]);

        $response = $this->get('/layanan');
        $siteData = $this->siteDataFromResponse($response->getContent());

        $response->assertOk();
        $this->assertSame('Layanan Published', $siteData['services'][0]['title']);
        $this->assertCount(1, $siteData['services']);
        $this->assertSame($siteData['services'], $siteData['service_apps']);
    }

    private function siteDataFromResponse(string $content): array
    {
        preg_match('/window\.__SITE_DATA__ = (.*?);\s*window\.Laravel/s', $content, $matches);

        $this->assertNotEmpty($matches[1] ?? null);

        return json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
    }
}
