<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_shortcuts', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('organizer');
            $table->text('description');
            $table->string('logo_path')->nullable();
            $table->string('link_url', 500);
            $table->string('link_target', 20)->default('_self');
            $table->string('status', 40)->default('draft');
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->timestamps();

            $table->index(['status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_shortcuts');
    }
};
