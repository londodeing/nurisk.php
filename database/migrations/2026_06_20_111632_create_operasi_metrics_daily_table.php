<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operasi_metrics_daily', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();
            $table->integer('login_count')->default(0);
            $table->integer('sync_success')->default(0);
            $table->integer('sync_failed')->default(0);
            $table->integer('sync_conflict_count')->default(0);
            $table->integer('bootstrap_count')->default(0);
            $table->integer('pdf_success')->default(0);
            $table->integer('pdf_failed')->default(0);
            $table->integer('queue_backlog_max')->default(0);
            $table->decimal('avg_sync_duration_ms', 10, 2)->default(0);
            $table->decimal('avg_bootstrap_duration_ms', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operasi_metrics_daily');
    }
};
