<?php

namespace Tests\Feature;

use App\Models\DepartmentNewsSetting;
use App\Models\User;
use App\Services\DepartmentNewsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DepartmentNewsWidgetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        Config::set('department_news.api_url', 'https://api.test/all-berita');
    }

    public function test_service_normalizes_api_news_and_applies_limit(): void
    {
        Http::fake([
            'https://api.test/*' => Http::response([
                'status' => 'success',
                'data' => [
                    [
                        'website' => 'babalan-lkt',
                        'judul' => 'Berita Lama',
                        'slug' => 'berita-lama',
                        'gambar' => 'lama.jpg',
                        'created_at' => '2026-05-10T05:13:44.000000Z',
                    ],
                    [
                        'website' => 'rsudtanjungpura',
                        'judul' => 'Berita Terbaru',
                        'slug' => 'berita-terbaru',
                        'gambar' => 'baru.jpeg',
                        'created_at' => '2026-05-19T06:30:06.000000Z',
                    ],
                    [
                        'website' => 'domain.tidak-valid',
                        'judul' => 'Harus Dilewati',
                        'slug' => 'harus-dilewati',
                        'gambar' => 'x.jpg',
                        'created_at' => '2026-05-20T06:30:06.000000Z',
                    ],
                ],
            ], 200),
        ]);

        $setting = DepartmentNewsSetting::query()->create([
            ...DepartmentNewsSetting::defaults(),
            'item_limit' => 2,
        ]);

        $items = app(DepartmentNewsService::class)->items($setting);

        $this->assertCount(2, $items);
        $this->assertSame('rsudtanjungpura', $items[0]['source']);
        $this->assertSame('Berita Terbaru', $items[0]['title']);
        $this->assertSame('https://rsudtanjungpura.langkatkab.go.id/storage/berita/baru.jpeg', $items[0]['image_url']);
        $this->assertSame('https://rsudtanjungpura.langkatkab.go.id/berita/berita-terbaru', $items[0]['link_url']);

        Http::assertSent(fn ($request): bool => $request['per_page'] === 2);
    }

    public function test_service_uses_last_successful_cache_when_api_fails(): void
    {
        $setting = DepartmentNewsSetting::query()->create(DepartmentNewsSetting::defaults());

        Http::fake([
            'https://api.test/*' => Http::response([
                'data' => [
                    [
                        'website' => 'arsip',
                        'judul' => 'Cache Pertama',
                        'slug' => 'cache-pertama',
                        'gambar' => 'cache.jpg',
                        'created_at' => '2026-05-01T00:00:00.000000Z',
                    ],
                ],
            ], 200),
        ]);

        $service = app(DepartmentNewsService::class);
        $firstItems = $service->items($setting);

        Http::fake([
            'https://api.test/*' => Http::response([], 500),
        ]);

        $fallbackItems = $service->refresh($setting);

        $this->assertSame($firstItems, $fallbackItems);
        $this->assertSame('Cache Pertama', $fallbackItems[0]['title']);
    }

    public function test_public_site_exposes_department_news_payload(): void
    {
        DepartmentNewsSetting::query()->create([
            ...DepartmentNewsSetting::defaults(),
            'title' => 'Kabar Perangkat Daerah',
            'item_limit' => 1,
        ]);

        Http::fake([
            'https://api.test/*' => Http::response([
                'data' => [
                    [
                        'website' => 'kuala-lkt',
                        'judul' => 'Camat Kuala Pimpin Upacara',
                        'slug' => 'camat-kuala-pimpin-upacara',
                        'gambar' => 'kuala.jpeg',
                        'created_at' => '2026-05-04T02:39:56.000000Z',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->get('/');
        $siteData = $this->siteDataFromResponse($response->getContent());

        $this->assertTrue($siteData['department_news']['enabled']);
        $this->assertSame('Kabar Perangkat Daerah', $siteData['department_news']['title']);
        $this->assertSame('kuala-lkt', $siteData['department_news']['items'][0]['source']);
    }

    public function test_editor_can_update_settings_and_clear_cache(): void
    {
        Http::fake([
            'https://api.test/*' => Http::response(['data' => []], 200),
        ]);

        $editor = User::factory()->create([
            'role' => User::ROLE_NEWS_EDITOR,
        ]);

        $this
            ->actingAs($editor)
            ->put('/admin/department-news', [
                'is_enabled' => '1',
                'title' => 'Kabar OPD',
                'description' => 'Info terbaru dari perangkat daerah.',
                'item_limit' => 9,
                'cache_ttl_minutes' => 15,
            ])
            ->assertRedirect(route('admin.department-news.edit'));

        $this->assertDatabaseHas('department_news_settings', [
            'title' => 'Kabar OPD',
            'description' => 'Info terbaru dari perangkat daerah.',
            'item_limit' => 9,
            'cache_ttl_minutes' => 15,
            'is_enabled' => true,
        ]);

        Cache::put('department_news.items.9', [['title' => 'cached']], now()->addMinutes(10));

        $this
            ->actingAs($editor)
            ->post('/admin/department-news/clear')
            ->assertRedirect(route('admin.department-news.edit'));

        $this->assertFalse(Cache::has('department_news.items.9'));
    }

    private function siteDataFromResponse(string $content): array
    {
        preg_match('/window\.__SITE_DATA__ = (.*?);\s*window\.Laravel/s', $content, $matches);

        $this->assertNotEmpty($matches[1] ?? null);

        return json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
    }
}
