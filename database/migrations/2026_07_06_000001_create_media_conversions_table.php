<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_conversions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('media_id');
            $table->string('conversion_type', 30);
            $table->string('disk', 50)->default('public');
            $table->string('path', 500);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->timestamps();

            $table->index('media_id');
            $table->index('conversion_type');

            $table->foreign('media_id')
                ->references('id')
                ->on('media')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_conversions');
    }
};
