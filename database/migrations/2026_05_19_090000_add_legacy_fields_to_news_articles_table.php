<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_articles', function (Blueprint $table): void {
            $table->unsignedBigInteger('legacy_id')->nullable()->unique()->after('id');
            $table->string('legacy_author', 120)->nullable()->after('published_by');
        });
    }

    public function down(): void
    {
        Schema::table('news_articles', function (Blueprint $table): void {
            $table->dropUnique(['legacy_id']);
            $table->dropColumn(['legacy_id', 'legacy_author']);
        });
    }
};
