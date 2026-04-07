<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->string('source_name', 180)->nullable()->after('featured_image');
            $table->string('source_url', 1000)->nullable()->after('source_name');
            $table->timestamp('source_published_at')->nullable()->after('source_url');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->dropColumn([
                'source_name',
                'source_url',
                'source_published_at',
            ]);
        });
    }
};
