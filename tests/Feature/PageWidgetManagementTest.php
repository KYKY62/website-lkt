<?php

namespace Tests\Feature;

use App\Models\PageWidget;
use App\Models\StaticPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PageWidgetManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_create_published_link_banner_widget_with_uploaded_image(): void
    {
        Storage::fake('public');

        $editor = User::factory()->create([
            'role' => User::ROLE_NEWS_EDITOR,
        ]);

        $response = $this
            ->actingAs($editor)
            ->post('/admin/widgets', [
                'title' => 'Banner Layanan Prioritas',
                'target_key' => PageWidget::TARGET_BUILTIN.':/berita',
                'column' => PageWidget::COLUMN_LEFT,
                'widget_type' => PageWidget::TYPE_LINK_BANNER,
                'status' => PageWidget::STATUS_PUBLISHED,
                'sort_order' => 2,
                'image' => UploadedFile::fake()->create('banner.jpg', 120, 'image/jpeg'),
                'image_alt' => 'Banner akses layanan',
                'link_url' => '/layanan',
                'link_target' => '_self',
            ]);

        $widget = PageWidget::query()->firstOrFail();

        $response->assertRedirect(route('admin.widgets.edit', $widget));

        $this->assertDatabaseHas('page_widgets', [
            'title' => 'Banner Layanan Prioritas',
            'target_type' => PageWidget::TARGET_BUILTIN,
            'target_path' => '/berita',
            'column' => PageWidget::COLUMN_LEFT,
            'widget_type' => PageWidget::TYPE_LINK_BANNER,
            'status' => PageWidget::STATUS_PUBLISHED,
            'sort_order' => 2,
            'link_url' => '/layanan',
        ]);

        Storage::disk('public')->assertExists($widget->image_path);
    }

    public function test_public_site_exposes_widgets_as_path_map_for_spa_navigation(): void
    {
        PageWidget::query()->create([
            'title' => 'CTA Berita',
            'target_type' => PageWidget::TARGET_BUILTIN,
            'target_path' => '/berita',
            'column' => PageWidget::COLUMN_LEFT,
            'widget_type' => PageWidget::TYPE_TEXT_CTA,
            'status' => PageWidget::STATUS_PUBLISHED,
            'sort_order' => 1,
            'link_url' => '/layanan',
            'link_target' => '_self',
            'text_body' => 'Akses layanan yang berkaitan dengan berita daerah.',
            'cta_label' => 'Buka Layanan',
        ]);

        PageWidget::query()->create([
            'title' => 'CTA Kontak',
            'target_type' => PageWidget::TARGET_BUILTIN,
            'target_path' => '/kontak',
            'column' => PageWidget::COLUMN_RIGHT,
            'widget_type' => PageWidget::TYPE_TEXT_CTA,
            'status' => PageWidget::STATUS_PUBLISHED,
            'sort_order' => 1,
            'link_url' => '/kontak',
            'link_target' => '_self',
            'text_body' => 'Hubungi kanal resmi Kabupaten Langkat.',
            'cta_label' => 'Buka Kontak',
        ]);

        $response = $this->get('/berita');

        $response->assertOk();

        $siteData = $this->siteDataFromResponse($response->getContent());

        $this->assertSame(
            'Akses layanan yang berkaitan dengan berita daerah.',
            $siteData['pre_footer_widgets']['/berita']['left'][0]['text_body']
        );
        $this->assertSame(
            'Hubungi kanal resmi Kabupaten Langkat.',
            $siteData['pre_footer_widgets']['/kontak']['right'][0]['text_body']
        );
    }

    public function test_html_is_sanitized_and_embed_domain_is_validated(): void
    {
        $superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $this
            ->actingAs($superAdmin)
            ->post('/admin/widgets', [
                'title' => 'HTML Informasi',
                'target_key' => PageWidget::TARGET_BUILTIN.':/',
                'column' => PageWidget::COLUMN_LEFT,
                'widget_type' => PageWidget::TYPE_HTML,
                'status' => PageWidget::STATUS_PUBLISHED,
                'sort_order' => 1,
                'html_content' => '<script>alert(1)</script><p onclick="alert(2)">Konten aman</p>',
            ])
            ->assertRedirect();

        $widget = PageWidget::query()->firstOrFail();

        $this->assertStringNotContainsString('<script>', $widget->html_content);
        $this->assertStringNotContainsString('onclick', $widget->html_content);
        $this->assertStringContainsString('Konten aman', $widget->html_content);

        $this
            ->actingAs($superAdmin)
            ->post('/admin/widgets', [
                'title' => 'Embed Tidak Valid',
                'target_key' => PageWidget::TARGET_BUILTIN.':/',
                'column' => PageWidget::COLUMN_RIGHT,
                'widget_type' => PageWidget::TYPE_EMBED,
                'status' => PageWidget::STATUS_PUBLISHED,
                'sort_order' => 1,
                'embed_url' => 'https://example.com/embed/test',
            ])
            ->assertSessionHasErrors('embed_url');
    }

    public function test_static_page_widgets_are_mapped_to_static_page_path(): void
    {
        $page = StaticPage::query()->create([
            'title' => 'Tentang Layanan',
            'slug' => 'tentang-layanan',
            'path' => '/tentang-layanan',
            'content' => '<p>Isi halaman statis.</p>',
            'status' => 'published',
        ]);

        PageWidget::query()->create([
            'static_page_id' => $page->id,
            'title' => 'CTA Halaman Statis',
            'target_type' => PageWidget::TARGET_STATIC_PAGE,
            'target_path' => null,
            'column' => PageWidget::COLUMN_LEFT,
            'widget_type' => PageWidget::TYPE_TEXT_CTA,
            'status' => PageWidget::STATUS_PUBLISHED,
            'sort_order' => 1,
            'link_url' => '/kontak',
            'link_target' => '_self',
            'text_body' => 'Widget khusus halaman statis.',
            'cta_label' => 'Hubungi Kami',
        ]);

        $response = $this->get('/tentang-layanan');

        $response
            ->assertOk()
            ->assertSee('Widget khusus halaman statis.');
    }

    public function test_home_hero_widgets_are_exposed_separately_from_pre_footer_widgets(): void
    {
        PageWidget::query()->create([
            'title' => 'Foto Pimpinan Daerah',
            'display_area' => PageWidget::AREA_HOME_HERO,
            'target_type' => PageWidget::TARGET_BUILTIN,
            'target_path' => '/',
            'column' => PageWidget::COLUMN_RIGHT,
            'widget_type' => PageWidget::TYPE_TEXT_CTA,
            'status' => PageWidget::STATUS_PUBLISHED,
            'sort_order' => 1,
            'link_url' => '/profil',
            'link_target' => '_self',
            'text_body' => 'Profil pimpinan Pemerintah Kabupaten Langkat.',
            'cta_label' => 'Lihat Profil',
        ]);

        $response = $this->get('/');
        $siteData = $this->siteDataFromResponse($response->getContent());

        $this->assertSame('Foto Pimpinan Daerah', $siteData['hero_widgets'][0]['title']);
        $this->assertArrayNotHasKey('/', $siteData['pre_footer_widgets']);
    }

    private function siteDataFromResponse(string $content): array
    {
        preg_match('/window\.__SITE_DATA__ = (.*?);\s*window\.Laravel/s', $content, $matches);

        $this->assertNotEmpty($matches[1] ?? null);

        return json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
    }
}
