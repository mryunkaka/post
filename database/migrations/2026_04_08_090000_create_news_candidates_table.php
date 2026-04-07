<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_candidates', function (Blueprint $table) {
            $table->id();
            $table->string('source_code', 120);
            $table->string('source_name', 180);
            $table->string('source_url', 1000);
            $table->char('source_url_hash', 64)->unique();
            $table->timestamp('source_published_at')->nullable();
            $table->string('region', 120)->nullable();
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->string('image_url', 1000)->nullable();
            $table->text('facts_summary')->nullable();
            $table->json('raw_payload')->nullable();
            $table->enum('status', ['pending', 'validated', 'rejected', 'drafted'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('article_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->index(['status', 'source_published_at']);
            $table->index('source_code');
            $table->index('region');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_candidates');
    }
};
