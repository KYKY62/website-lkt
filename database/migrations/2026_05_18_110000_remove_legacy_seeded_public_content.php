<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('news_articles')
            ->whereIn('slug', [
                'langkat-perkuat-pelayanan-digital',
                'koordinasi-lintas-opd-untuk-portal-terpadu',
                'penguatan-konten-publik-dan-pengumuman-resmi',
                'ruang-galeri-dan-potensi-daerah',
            ])
            ->delete();

        $legacyAdminEmail = 'admin'.'@'.'example.com';
        $legacyAdminName = 'Admin'.' '.'Demo';

        if (! DB::table('users')->where('email', 'admin@langkatkab.go.id')->exists()) {
            DB::table('users')
                ->where('email', $legacyAdminEmail)
                ->where('name', $legacyAdminName)
                ->update([
                    'name' => 'Super Admin',
                    'email' => 'admin@langkatkab.go.id',
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        //
    }
};
