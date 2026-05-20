<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE news_articles MODIFY title VARCHAR(500) NOT NULL');
            DB::statement('ALTER TABLE news_articles MODIFY slug VARCHAR(220) NOT NULL');

            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('news_articles', function (Blueprint $table): void {
            $table->string('title', 500)->change();
            $table->string('slug', 220)->change();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE news_articles MODIFY title VARCHAR(180) NOT NULL');
            DB::statement('ALTER TABLE news_articles MODIFY slug VARCHAR(180) NOT NULL');

            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('news_articles', function (Blueprint $table): void {
            $table->string('title', 180)->change();
            $table->string('slug', 180)->change();
        });
    }
};
