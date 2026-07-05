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
        Schema::table('org_assets', function (Blueprint $table) {
            $table->string('legal_owner_name')->nullable()->after('name');
            $table->unsignedBigInteger('affiliated_node_id')->nullable()->after('custodian_node_id');
            $table->string('verification_status')->default('UNVERIFIED')->after('readiness');

            $table->foreign('affiliated_node_id')->references('id')->on('org_nodes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('org_assets', function (Blueprint $table) {
            $table->dropForeign(['affiliated_node_id']);
            $table->dropColumn(['legal_owner_name', 'affiliated_node_id', 'verification_status']);
        });
    }
};
