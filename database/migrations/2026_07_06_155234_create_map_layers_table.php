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
        Schema::create('map_layers', function (Blueprint $table) {
            $table->id();
            $table->string('layer_id')->unique();
            $table->string('name');
            $table->string('category')->default('hazard'); // hazard, incident, resource, weather, organization
            $table->string('render_type')->default('geojson_point'); // geojson_point, geojson_polygon, raster_tile, heatmap
            $table->string('source_url')->nullable(); // external source URL if applicable
            $table->string('source_type')->default('internal'); // internal, inarisk, bmkg, etc.
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(true);
            $table->integer('display_order')->default(0);
            $table->integer('refresh_interval_minutes')->nullable();
            $table->integer('cache_ttl')->default(3600); // in seconds
            $table->json('legend_json')->nullable();
            $table->json('style_json')->nullable();
            $table->json('metadata')->nullable(); // Additional config (colors, icons, zoom levels)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('map_layers');
    }
};
