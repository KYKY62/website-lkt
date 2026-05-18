<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_access_user_management(): void
    {
        $superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $response = $this
            ->actingAs($superAdmin)
            ->get('/admin/users');

        $response
            ->assertOk()
            ->assertSee('Manajemen Pengguna Admin');
    }

    public function test_news_editor_cannot_access_user_management(): void
    {
        $editor = User::factory()->create([
            'role' => User::ROLE_NEWS_EDITOR,
        ]);

        $response = $this
            ->actingAs($editor)
            ->get('/admin/users');

        $response->assertForbidden();
    }

    public function test_super_admin_can_create_news_editor(): void
    {
        $superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $response = $this
            ->actingAs($superAdmin)
            ->post('/admin/users', [
                'name' => 'Editor Berita',
                'email' => 'editor@example.com',
                'role' => User::ROLE_NEWS_EDITOR,
                'password' => 'editor12345',
                'password_confirmation' => 'editor12345',
            ]);

        $user = User::query()->where('email', 'editor@example.com')->firstOrFail();

        $response->assertRedirect(route('admin.users.edit', $user));

        $this->assertDatabaseHas('users', [
            'email' => 'editor@example.com',
            'role' => User::ROLE_NEWS_EDITOR,
        ]);
    }

    public function test_news_editor_can_access_news_management(): void
    {
        $editor = User::factory()->create([
            'role' => User::ROLE_NEWS_EDITOR,
        ]);

        $response = $this
            ->actingAs($editor)
            ->get('/admin/news');

        $response
            ->assertOk()
            ->assertSee('Manajemen Berita');
    }

    public function test_news_editor_cannot_access_pages_and_menu_management(): void
    {
        $editor = User::factory()->create([
            'role' => User::ROLE_NEWS_EDITOR,
        ]);

        $this->actingAs($editor)->get('/admin/pages')->assertForbidden();
        $this->actingAs($editor)->get('/admin/menus')->assertForbidden();
    }
}
