<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_tombstones', function (Blueprint $table) {
            $table->id('id_tombstone');
            $table->string('entity_type')->index(); // e.g. 'operasi_penugasan'
            $table->string('uuid_entity')->index(); // entity UUID
            $table->timestamp('deleted_at')->useCurrent();
            $table->unsignedBigInteger('deleted_by')->nullable()->index();
            $table->text('alasan_hapus')->nullable();
            $table->bigInteger('cursor_value')->index(); // cursor value from sync_cursors

            $table->foreign('deleted_by', 'fk_sync_tombstones_deleted_by')
                ->references('id_pengguna')
                ->on('auth_users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_tombstones');
    }
};
