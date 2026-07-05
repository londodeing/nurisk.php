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
        Schema::create('sync_conflicts', function (Blueprint $table) {
            $table->id();
            $table->string('device_uuid');
            $table->string('entity_type')->index();
            $table->string('uuid_entity')->index();
            $table->integer('client_version');
            $table->integer('server_version');
            $table->json('client_data');
            $table->json('server_data');
            $table->timestamp('resolved_at')->nullable();
            
            $table->timestamp('dibuat_pada')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_conflicts');
    }
};
