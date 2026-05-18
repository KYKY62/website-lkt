<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            NewsArticleSeeder::class,
        ]);

        $adminEmail = env('ADMIN_SEED_EMAIL');
        $adminPassword = env('ADMIN_SEED_PASSWORD');

        if (! $adminEmail || ! $adminPassword) {
            return;
        }

        User::query()->updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => env('ADMIN_SEED_NAME', 'Super Admin'),
                'password' => $adminPassword,
                'role' => User::ROLE_SUPER_ADMIN,
                'email_verified_at' => now(),
            ]
        );
    }
}
