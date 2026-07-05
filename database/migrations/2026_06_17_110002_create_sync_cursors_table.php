<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_cursors', function (Blueprint $table) {
            $table->id('id_cursor');
            $table->string('entity_type')->index(); // e.g. 'operasi_penugasan'
            $table->string('uuid_entity')->index(); // entity UUID
            $table->bigInteger('cursor_value')->index(); // sequential cursor value
            $table->string('action'); // 'create', 'update'
            
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->nullable()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_cursors');
    }
};
