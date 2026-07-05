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
        Schema::create('org_assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code', 100)->unique();
            $table->string('name', 255);
            $table->string('category', 50); // FACILITY, FLEET, EQUIPMENT, INVENTORY, DISASTER_SPECIFIC
            $table->string('sub_category', 100)->nullable();
            
            // Governance and Ownership
            $table->unsignedBigInteger('owner_node_id');
            $table->unsignedBigInteger('custodian_node_id')->nullable();
            
            // Territorial Bound
            $table->string('home_territory_code', 20);
            $table->string('current_territory_code', 20)->nullable();
            
            // Asset State and Readiness
            $table->string('status', 50)->default('DRAFT'); // DRAFT, ACTIVE, MAINTENANCE, DAMAGED, LOST, DISPOSED
            $table->string('readiness', 50)->default('UNAVAILABLE'); // READY, DEGRADED, UNAVAILABLE, OUT_OF_SERVICE
            
            // Polymorphic/Flexible attributes
            $table->json('metadata')->nullable();
            
            $table->timestamps();

            $table->foreign('owner_node_id')->references('id')->on('org_nodes')->onDelete('cascade');
            $table->foreign('custodian_node_id')->references('id')->on('org_nodes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_assets');
    }
};
