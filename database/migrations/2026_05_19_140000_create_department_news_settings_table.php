<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_news_settings', function (Blueprint $table): void {
            $table->id();
            $table->boolean('is_enabled')->default(true);
            $table->string('title', 180)->default('Kabar Perangkat Daerah');
            $table->string('description', 300)->nullable();
            $table->unsignedTinyInteger('item_limit')->default(7);
            $table->unsignedSmallInteger('cache_ttl_minutes')->default(10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_news_settings');
    }
};
