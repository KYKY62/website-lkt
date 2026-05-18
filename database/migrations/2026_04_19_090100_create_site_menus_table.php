<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('site_menus')->nullOnDelete();
            $table->foreignId('page_id')->nullable()->constrained('static_pages')->nullOnDelete();
            $table->string('label', 120);
            $table->string('item_type', 20);
            $table->string('url', 500)->nullable();
            $table->string('target', 20)->default('_self');
            $table->string('module_key', 40)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['parent_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_menus');
    }
};
