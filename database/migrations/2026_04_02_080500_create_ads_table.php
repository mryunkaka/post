<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('placement', 100);
            $table->enum('type', ['adsense', 'banner', 'html', 'affiliate', 'sponsored'])->default('banner');
            $table->longText('code')->nullable();
            $table->string('image_url')->nullable();
            $table->string('target_url')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('placement', 'idx_ads_placement');
            $table->index('type', 'idx_ads_type');
            $table->index('is_active', 'idx_ads_is_active');
            $table->index('start_at', 'idx_ads_start_at');
            $table->index('end_at', 'idx_ads_end_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
