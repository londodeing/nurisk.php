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
        Schema::create('mobile_sync_queues', function (Blueprint $table) {
            $table->id();
            $table->uuid('request_id')->unique();
            $table->string('device_uuid')->index();
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->enum('status', ['pending', 'processed', 'failed'])->default('pending')->index();
            $table->timestamp('processed_at')->nullable();
            
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->nullable()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_sync_queues');
    }
};
