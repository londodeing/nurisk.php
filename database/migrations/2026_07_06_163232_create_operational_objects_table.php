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
        Schema::create('operational_objects', function (Blueprint $table) {
            $table->string('id')->primary(); // Ex: INC-123
            $table->string('object_type')->index(); // incident, posko, volunteer, asset
            $table->string('status')->index(); // VERIFIED, RESPONSE, dll
            $table->string('title');
            $table->text('summary')->nullable();
            
            // Spatial
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            // Alternatively $table->geometry('geom') can be used, but lat/lng is simpler for JSON APIs
            
            // Render specific
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->integer('priority')->default(0)->index(); // For Z-index Layer Priority
            
            // Serialized View Models
            $table->json('popup_json')->nullable();
            $table->json('timeline_json')->nullable();
            $table->json('dashboard_json')->nullable();
            
            $table->json('permissions')->nullable(); // RBA limits
            $table->integer('refresh_interval')->default(60);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operational_objects');
    }
};
