<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_devices', function (Blueprint $table) {
            $table->id('id_device');
            $table->string('uuid_device')->unique();
            $table->unsignedBigInteger('id_pengguna')->index();
            $table->string('platform'); // android, ios, etc.
            $table->string('app_version');
            $table->timestamp('last_sync_at')->nullable();
            $table->string('push_token')->nullable();
            $table->string('status')->default('active'); // active, revoked, inactive
            
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->nullable()->useCurrentOnUpdate();

            $table->foreign('id_pengguna', 'fk_mobile_devices_id_pengguna')
                ->references('id_pengguna')
                ->on('auth_users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_devices');
    }
};
