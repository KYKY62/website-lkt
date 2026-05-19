<?php

namespace Tests\Feature;

use App\Models\SiteMenu;
use App\Models\StaticPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminContentStructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_static_page(): void
    {
        $superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $response = $this
            ->actingAs($superAdmin)
            ->post('/admin/pages', [
                'title' => 'Tentang Portal',
                'slug' => '',
                'path' => '/tentang-portal',
                'excerpt' => 'Ringkasan halaman statis.',
                'content' => '<p>Isi halaman statis.</p>',
                'status' => 'published',
            ]);

        $page = StaticPage::query()->firstOrFail();

        $response->assertRedirect(route('admin.pages.edit', $page));

        $this->assertDatabaseHas('static_pages', [
            'title' => 'Tentang Portal',
            'path' => '/tentang-portal',
            'status' => 'published',
        ]);
    }

    public function test_super_admin_can_create_page_and_submenu_structure(): void
    {
        $superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $page = StaticPage::query()->create([
            'title' => 'Profil Organisasi',
            'slug' => 'profil-organisasi',
            'path' => '/profil-organisasi',
            'content' => '<p>Isi halaman.</p>',
            'status' => 'published',
        ]);

        $masterResponse = $this
            ->actingAs($superAdmin)
            ->post('/admin/menus', [
                'label' => 'Profil',
                'menu_position' => 'master',
                'item_type' => SiteMenu::TYPE_MODULE,
                'module_key' => 'services',
                'sort_order' => 1,
                'is_active' => '1',
            ]);

        $masterMenu = SiteMenu::query()->where('label', 'Profil')->latest('id')->firstOrFail();
        $masterResponse->assertRedirect(route('admin.menus.edit', $masterMenu));

        $submenuResponse = $this
            ->actingAs($superAdmin)
            ->post('/admin/menus', [
                'label' => 'Profil Organisasi',
                'menu_position' => 'submenu',
                'parent_id' => $masterMenu->id,
                'item_type' => SiteMenu::TYPE_PAGE,
                'page_id' => $page->id,
                'sort_order' => 2,
                'is_active' => '1',
            ]);

        $submenu = SiteMenu::query()->where('label', 'Profil Organisasi')->firstOrFail();
        $submenuResponse->assertRedirect(route('admin.menus.edit', $submenu));

        $this->assertDatabaseHas('site_menus', [
            'label' => 'Profil Organisasi',
            'parent_id' => $masterMenu->id,
            'item_type' => SiteMenu::TYPE_PAGE,
            'page_id' => $page->id,
        ]);
    }

    public function test_public_site_uses_database_pages_and_navigation(): void
    {
        $page = StaticPage::query()->create([
            'title' => 'Tentang Portal',
            'slug' => 'tentang-portal',
            'path' => '/tentang-portal',
            'excerpt' => 'Halaman statis publik.',
            'content' => '<p>Isi halaman statis publik.</p>',
            'status' => 'published',
        ]);

        $masterMenu = SiteMenu::query()->create([
            'label' => 'Informasi',
            'item_type' => SiteMenu::TYPE_LINK,
            'url' => '/informasi',
            'target' => '_self',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        SiteMenu::query()->create([
            'label' => 'Tentang Portal',
            'parent_id' => $masterMenu->id,
            'page_id' => $page->id,
            'item_type' => SiteMenu::TYPE_PAGE,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        SiteMenu::query()->create([
            'label' => 'Berita',
            'item_type' => SiteMenu::TYPE_MODULE,
            'module_key' => 'news',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $response = $this->get('/tentang-portal');

        $response
            ->assertOk()
            ->assertSee('Tentang Portal')
            ->assertSee('Isi halaman statis publik.')
            ->assertSee('Informasi')
            ->assertSee('Berita');
    }

    public function test_default_site_menus_are_seeded_for_admin_management(): void
    {
        $this->assertDatabaseHas('site_menus', [
            'label' => 'Beranda',
            'item_type' => SiteMenu::TYPE_MODULE,
            'module_key' => 'home',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('site_menus', [
            'label' => 'Profil',
            'item_type' => SiteMenu::TYPE_MODULE,
            'module_key' => 'profile',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('site_menus', [
            'label' => 'Kontak',
            'item_type' => SiteMenu::TYPE_MODULE,
            'module_key' => 'contact',
            'sort_order' => 8,
            'is_active' => true,
        ]);
    }

    public function test_super_admin_can_reorder_master_menu_and_submenu(): void
    {
        $superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $firstMaster = SiteMenu::query()->create([
            'label' => 'Profil',
            'item_type' => SiteMenu::TYPE_MODULE,
            'module_key' => 'services',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $secondMaster = SiteMenu::query()->create([
            'label' => 'Informasi',
            'item_type' => SiteMenu::TYPE_MODULE,
            'module_key' => 'news',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $firstChild = SiteMenu::query()->create([
            'label' => 'Sejarah',
            'parent_id' => $firstMaster->id,
            'item_type' => SiteMenu::TYPE_LINK,
            'url' => '/sejarah',
            'target' => '_self',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $secondChild = SiteMenu::query()->create([
            'label' => 'Visi Misi',
            'parent_id' => $firstMaster->id,
            'item_type' => SiteMenu::TYPE_LINK,
            'url' => '/visi-misi',
            'target' => '_self',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($superAdmin)
            ->postJson('/admin/menus/reorder', [
                'tree' => [
                    [
                        'id' => $secondMaster->id,
                        'children' => [],
                    ],
                    [
                        'id' => $firstMaster->id,
                        'children' => [
                            ['id' => $secondChild->id],
                            ['id' => $firstChild->id],
                        ],
                    ],
                ],
            ]);

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Urutan menu berhasil diperbarui.',
            ]);

        $this->assertDatabaseHas('site_menus', [
            'id' => $secondMaster->id,
            'parent_id' => null,
            'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('site_menus', [
            'id' => $firstMaster->id,
            'parent_id' => null,
            'sort_order' => 2,
        ]);

        $this->assertDatabaseHas('site_menus', [
            'id' => $secondChild->id,
            'parent_id' => $firstMaster->id,
            'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('site_menus', [
            'id' => $firstChild->id,
            'parent_id' => $firstMaster->id,
            'sort_order' => 2,
        ]);
    }
}
