<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $response = $this->get('/admin/news');

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_login_and_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'superadmin@langkatkab.go.id',
            'password' => 'passwordAdmin123',
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $loginResponse = $this->post('/admin/login', [
            'email' => 'superadmin@langkatkab.go.id',
            'password' => 'passwordAdmin123',
        ]);

        $loginResponse->assertRedirect(route('admin.news.index'));
        $this->assertAuthenticatedAs($user);

        $logoutResponse = $this->post('/admin/logout');

        $logoutResponse->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_admin_can_update_profile_and_password(): void
    {
        $user = User::factory()->create([
            'name' => 'Admin Lama',
            'email' => 'superadmin@langkatkab.go.id',
            'password' => 'passwordAdmin123',
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $profileResponse = $this
            ->actingAs($user)
            ->put('/admin/account/profile', [
                'name' => 'Admin Baru',
                'email' => 'baru@example.com',
            ]);

        $profileResponse->assertRedirect(route('admin.account.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Admin Baru',
            'email' => 'baru@example.com',
        ]);

        $passwordResponse = $this
            ->actingAs($user->fresh())
            ->put('/admin/account/password', [
                'current_password' => 'passwordAdmin123',
                'password' => 'passwordBaru123',
                'password_confirmation' => 'passwordBaru123',
            ]);

        $passwordResponse->assertRedirect(route('admin.account.edit'));

        $this->assertTrue(Hash::check('passwordBaru123', $user->fresh()->password));
    }
}
