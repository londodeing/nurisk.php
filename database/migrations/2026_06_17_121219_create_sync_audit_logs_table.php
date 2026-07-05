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
        Schema::create('sync_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('device_uuid')->index();
            $table->uuid('request_id')->index();
            $table->integer('entities_synced')->default(0);
            $table->integer('duration_ms')->default(0);
            $table->string('status');
            
            $table->timestamp('dibuat_pada')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_audit_logs');
    }
};
