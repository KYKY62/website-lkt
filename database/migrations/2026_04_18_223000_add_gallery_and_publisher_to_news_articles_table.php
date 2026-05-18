<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_articles', function (Blueprint $table) {
            $table->json('image_urls')->nullable()->after('cover_image_url');
            $table->foreignId('published_by')
                ->nullable()
                ->after('published_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('news_articles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('published_by');
            $table->dropColumn('image_urls');
        });
    }
};
