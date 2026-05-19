<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('site_menus') || DB::table('site_menus')->exists()) {
            return;
        }

        $now = now();

        DB::table('site_menus')->insert([
            [
                'label' => 'Beranda',
                'item_type' => 'module',
                'module_key' => 'home',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'label' => 'Profil',
                'item_type' => 'module',
                'module_key' => 'profile',
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'label' => 'Berita',
                'item_type' => 'module',
                'module_key' => 'news',
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'label' => 'Pengumuman',
                'item_type' => 'module',
                'module_key' => 'announcements',
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'label' => 'Layanan',
                'item_type' => 'module',
                'module_key' => 'services',
                'sort_order' => 5,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'label' => 'Galeri',
                'item_type' => 'module',
                'module_key' => 'gallery',
                'sort_order' => 6,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'label' => 'Download',
                'item_type' => 'module',
                'module_key' => 'downloads',
                'sort_order' => 7,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'label' => 'Kontak',
                'item_type' => 'module',
                'module_key' => 'contact',
                'sort_order' => 8,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        // Intentionally left blank so rollback never deletes menu changes made by admins.
    }
};
