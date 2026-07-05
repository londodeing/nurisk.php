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
        Schema::create('weather_snapshots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('territory_code', 50);
            $table->enum('territory_type', ['pwnu', 'pcnu', 'mwc']);
            $table->unsignedInteger('territory_id');
            $table->string('provider', 50)->default('openweathermap');
            $table->json('current_weather')->nullable();
            $table->json('hourly_forecast')->nullable();
            $table->json('daily_forecast')->nullable();
            $table->json('risk_analysis')->nullable();
            $table->timestamp('cached_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('territory_code', 'idx_ws_territory');
            $table->index('expires_at', 'idx_ws_expires');
            $table->index(['territory_type', 'territory_id'], 'idx_ws_scope');
            $table->unique(['territory_code', 'provider'], 'uq_ws_territory_provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_snapshots');
    }
};
