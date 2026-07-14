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
        Schema::create('execution_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('decision_draft_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->foreignId('assignee_id')->nullable()->constrained('auth_users', 'id_pengguna'); // PIC
            $table->enum('status', ['TODO', 'IN_PROGRESS', 'DONE', 'CANCELLED'])->default('TODO');
            $table->dateTime('deadline')->nullable();
            $table->text('evidence_payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('execution_tasks');
    }
};
