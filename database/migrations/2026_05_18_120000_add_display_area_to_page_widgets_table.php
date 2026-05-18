<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_widgets', function (Blueprint $table): void {
            $table->string('display_area', 40)
                ->default('pre_footer')
                ->after('static_page_id')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('page_widgets', function (Blueprint $table): void {
            $table->dropColumn('display_area');
        });
    }
};
