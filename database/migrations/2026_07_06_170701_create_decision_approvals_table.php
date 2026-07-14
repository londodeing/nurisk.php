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
        Schema::create('decision_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('decision_draft_id')->constrained()->cascadeOnDelete();
            $table->foreignId('approver_id')->constrained('auth_users', 'id_pengguna'); // The Chairman
            $table->enum('status', ['PENDING', 'APPROVED', 'REVISION_REQUESTED', 'REJECTED'])->default('PENDING');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('decision_approvals');
    }
};
