<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_widgets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('static_page_id')->nullable()->constrained('static_pages')->nullOnDelete();
            $table->string('title');
            $table->string('target_type', 40);
            $table->string('target_path')->nullable();
            $table->string('column', 20);
            $table->string('widget_type', 40);
            $table->string('status', 40)->default('draft');
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->string('image_path')->nullable();
            $table->string('image_alt')->nullable();
            $table->string('link_url', 500)->nullable();
            $table->string('link_target', 20)->default('_self');
            $table->text('html_content')->nullable();
            $table->string('embed_url', 800)->nullable();
            $table->text('text_body')->nullable();
            $table->string('cta_label')->nullable();
            $table->timestamps();

            $table->index(['target_type', 'target_path', 'column', 'sort_order']);
            $table->index(['static_page_id', 'column', 'sort_order']);
            $table->index(['status', 'widget_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_widgets');
    }
};
