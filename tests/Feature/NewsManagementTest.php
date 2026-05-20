<?php

namespace Tests\Feature;

use App\Models\NewsArticle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewsManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_news_index_loads(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/admin/news');

        $response
            ->assertOk()
            ->assertSee('Manajemen Berita');
    }

    public function test_admin_can_create_update_and_delete_news_article_with_uploaded_images(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $createResponse = $this
            ->actingAs($user)
            ->post('/admin/news', [
                'title' => 'Portal Baru Siap Digunakan',
                'slug' => '',
                'category' => 'Transformasi Digital',
                'excerpt' => 'Ringkasan berita untuk daftar publik.',
                'content' => '<p>Paragraf pertama.</p><p>Paragraf kedua.</p>',
                'images' => [
                    UploadedFile::fake()->create('cover.jpg', 120, 'image/jpeg'),
                    UploadedFile::fake()->create('gallery-1.jpg', 160, 'image/jpeg'),
                ],
                'status' => 'published',
                'published_at' => '2026-04-18 20:30:00',
            ]);

        $article = NewsArticle::query()->firstOrFail();

        $createResponse
            ->assertRedirect(route('admin.news.edit', $article));

        $this->assertDatabaseHas('news_articles', [
            'title' => 'Portal Baru Siap Digunakan',
            'slug' => 'portal-baru-siap-digunakan',
            'status' => 'published',
            'published_by' => $user->id,
        ]);

        $createdImages = $article->fresh()->image_urls;

        $this->assertCount(2, $createdImages);
        Storage::disk('public')->assertExists($createdImages[0]);
        Storage::disk('public')->assertExists($createdImages[1]);
        $this->assertSame($createdImages[0], $article->fresh()->cover_image_url);

        $updateResponse = $this
            ->actingAs($user)
            ->put("/admin/news/{$article->id}", [
                'title' => 'Portal Baru Resmi Diperbarui',
                'slug' => 'portal-baru-resmi-diperbarui',
                'category' => 'Pemerintahan',
                'excerpt' => 'Ringkasan yang diperbarui.',
                'content' => '<h2>Isi yang diperbarui.</h2><p>Paragraf lanjutan.</p>',
                'images' => [
                    UploadedFile::fake()->create('foto-utama-baru.jpg', 180, 'image/jpeg'),
                ],
                'status' => 'draft',
                'published_at' => '',
            ]);

        $updateResponse
            ->assertRedirect(route('admin.news.edit', $article));

        $updatedArticle = $article->fresh();

        $this->assertDatabaseHas('news_articles', [
            'id' => $article->id,
            'title' => 'Portal Baru Resmi Diperbarui',
            'slug' => 'portal-baru-resmi-diperbarui',
            'status' => 'draft',
            'published_by' => null,
        ]);

        $this->assertCount(1, $updatedArticle->image_urls);
        $this->assertSame($updatedArticle->image_urls[0], $updatedArticle->cover_image_url);
        Storage::disk('public')->assertExists($updatedArticle->image_urls[0]);
        Storage::disk('public')->assertMissing($createdImages[0]);
        Storage::disk('public')->assertMissing($createdImages[1]);

        $storedPath = $updatedArticle->image_urls[0];

        $deleteResponse = $this
            ->actingAs($user)
            ->delete("/admin/news/{$article->id}");

        $deleteResponse
            ->assertRedirect(route('admin.news.index'));

        $this->assertDatabaseMissing('news_articles', [
            'id' => $article->id,
        ]);
        Storage::disk('public')->assertMissing($storedPath);
    }

    public function test_public_site_uses_published_database_news_with_editor_and_uploaded_gallery(): void
    {
        Storage::fake('public');

        $editor = User::factory()->create([
            'name' => 'Editor Uji',
        ]);

        $primaryImage = UploadedFile::fake()->create('utama.jpg', 120, 'image/jpeg')->store('news-gallery', 'public');
        $detailImage = UploadedFile::fake()->create('detail.jpg', 140, 'image/jpeg')->store('news-gallery', 'public');

        NewsArticle::query()->create([
            'title' => 'Berita Draft',
            'slug' => 'berita-draft',
            'category' => 'Internal',
            'excerpt' => 'Tidak boleh tampil.',
            'content' => 'Konten draft',
            'status' => 'draft',
        ]);

        NewsArticle::query()->create([
            'title' => 'Berita Published',
            'slug' => 'berita-published',
            'category' => 'Informasi Publik',
            'excerpt' => 'Harus tampil di website.',
            'content' => '<p>Paragraf satu.</p><p>Paragraf dua.</p>',
            'cover_image_url' => $primaryImage,
            'image_urls' => [$primaryImage, $detailImage],
            'status' => 'published',
            'published_at' => now(),
            'published_by' => $editor->id,
        ]);

        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('Berita Published')
            ->assertSee('Editor Uji')
            ->assertSee('\/storage\/news-gallery\/', false)
            ->assertDontSee('Berita Draft');
    }

    public function test_public_news_api_paginates_list_and_serves_detail_payload(): void
    {
        $editor = User::factory()->create([
            'name' => 'Editor API',
        ]);

        foreach (range(1, 12) as $index) {
            NewsArticle::query()->create([
                'title' => "Berita API {$index}",
                'slug' => "berita-api-{$index}",
                'category' => 'Informasi Publik',
                'excerpt' => "Ringkasan berita {$index}",
                'content' => "<p>Konten berita {$index}</p>",
                'status' => 'published',
                'published_at' => now()->subMinutes($index),
                'published_by' => $editor->id,
            ]);
        }

        $homeResponse = $this->get('/');
        preg_match('/window\.__SITE_DATA__ = (.*?);\s*window\.Laravel/s', $homeResponse->getContent(), $matches);
        $siteData = json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);

        $this->assertCount(9, $siteData['news']);
        $this->assertArrayNotHasKey('content_html', $siteData['news'][0]);

        $firstPage = $this->getJson('/api/news?per_page=10');

        $firstPage
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.has_more', true)
            ->assertJsonPath('meta.next_page', 2);

        $firstPageData = $firstPage->json('data');
        $this->assertSame('berita-api-1', $firstPageData[0]['slug']);
        $this->assertArrayNotHasKey('content_html', $firstPageData[0]);

        $secondPage = $this->getJson('/api/news?page=2&per_page=10');

        $secondPage
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.has_more', false);

        $detail = $this->getJson('/api/news/berita-api-1');

        $detail
            ->assertOk()
            ->assertJsonPath('slug', 'berita-api-1')
            ->assertJsonPath('editor_name', 'Editor API');

        $this->assertArrayHasKey('content_html', $detail->json());
        $this->assertArrayHasKey('gallery_images', $detail->json());
    }

    public function test_admin_can_reorder_existing_uploaded_images(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $firstImage = UploadedFile::fake()->create('awal-1.jpg', 120, 'image/jpeg')->store('news-gallery', 'public');
        $secondImage = UploadedFile::fake()->create('awal-2.jpg', 140, 'image/jpeg')->store('news-gallery', 'public');
        $thirdImage = UploadedFile::fake()->create('awal-3.jpg', 160, 'image/jpeg')->store('news-gallery', 'public');

        $article = NewsArticle::query()->create([
            'title' => 'Berita Urut Gambar',
            'slug' => 'berita-urut-gambar',
            'category' => 'Informasi Publik',
            'excerpt' => 'Uji pengurutan gambar lama.',
            'content' => '<p>Isi berita.</p>',
            'cover_image_url' => $firstImage,
            'image_urls' => [$firstImage, $secondImage, $thirdImage],
            'status' => 'published',
            'published_at' => now(),
            'published_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->put("/admin/news/{$article->id}", [
                'title' => 'Berita Urut Gambar',
                'slug' => 'berita-urut-gambar',
                'category' => 'Informasi Publik',
                'excerpt' => 'Uji pengurutan gambar lama.',
                'content' => '<p>Isi berita.</p>',
                'existing_image_order' => [$thirdImage, $firstImage, $secondImage],
                'status' => 'published',
                'published_at' => now()->format('Y-m-d H:i:s'),
            ]);

        $response->assertRedirect(route('admin.news.edit', $article));

        $updatedArticle = $article->fresh();

        $this->assertSame([$thirdImage, $firstImage, $secondImage], $updatedArticle->image_urls);
        $this->assertSame($thirdImage, $updatedArticle->cover_image_url);
        Storage::disk('public')->assertExists($firstImage);
        Storage::disk('public')->assertExists($secondImage);
        Storage::disk('public')->assertExists($thirdImage);
    }

    public function test_admin_can_delete_existing_images_without_reuploading_gallery(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $firstImage = UploadedFile::fake()->create('hapus-1.jpg', 120, 'image/jpeg')->store('news-gallery', 'public');
        $secondImage = UploadedFile::fake()->create('hapus-2.jpg', 140, 'image/jpeg')->store('news-gallery', 'public');
        $thirdImage = UploadedFile::fake()->create('hapus-3.jpg', 160, 'image/jpeg')->store('news-gallery', 'public');

        $article = NewsArticle::query()->create([
            'title' => 'Berita Hapus Gambar',
            'slug' => 'berita-hapus-gambar',
            'category' => 'Informasi Publik',
            'excerpt' => 'Uji hapus gambar lama.',
            'content' => '<p>Isi berita.</p>',
            'cover_image_url' => $firstImage,
            'image_urls' => [$firstImage, $secondImage, $thirdImage],
            'status' => 'published',
            'published_at' => now(),
            'published_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->put("/admin/news/{$article->id}", [
                'title' => 'Berita Hapus Gambar',
                'slug' => 'berita-hapus-gambar',
                'category' => 'Informasi Publik',
                'excerpt' => 'Uji hapus gambar lama.',
                'content' => '<p>Isi berita.</p>',
                'existing_image_order' => [$secondImage],
                'status' => 'published',
                'published_at' => now()->format('Y-m-d H:i:s'),
            ]);

        $response->assertRedirect(route('admin.news.edit', $article));

        $updatedArticle = $article->fresh();

        $this->assertSame([$secondImage], $updatedArticle->image_urls);
        $this->assertSame($secondImage, $updatedArticle->cover_image_url);
        Storage::disk('public')->assertMissing($firstImage);
        Storage::disk('public')->assertExists($secondImage);
        Storage::disk('public')->assertMissing($thirdImage);
    }
}
