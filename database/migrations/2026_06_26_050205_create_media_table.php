<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 50);
            $table->unsignedBigInteger('entity_id')->nullable()->index();
            $table->string('disk', 50)->default('public');
            $table->string('path', 500);
            $table->string('media_type', 20)->default('IMAGE');
            $table->unsignedTinyInteger('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->string('access_level', 20)->default('PUBLIC');
            $table->string('original_name', 255)->nullable();
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->char('hash_sha256', 64)->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->string('uploaded_ip', 45)->nullable();
            $table->text('uploaded_user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id'], 'media_entity_idx');
            $table->index('hash_sha256', 'media_hash_idx');
            $table->index('media_type');
            $table->index('access_level');
            $table->index('is_active');
            $table->index('version');
            $table->index(['entity_type', 'entity_id', 'is_active'], 'media_active_entity_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
