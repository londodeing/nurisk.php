<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Governance Workflow Layer — Meeting Domain
 *
 * 5 tables:
 * - meeting_sessions       : Rapat/Pleno (standalone, tidak terikat insiden)
 * - meeting_agendas        : Agenda rapat
 * - meeting_attendees      : Peserta rapat (mandate-based)
 * - meeting_votes          : Voting per agenda (immutable)
 * - meeting_minutes        : Notulensi (lifecycle: draft→review→approved→locked)
 *
 * SEMUA TABEL:
 * - UUID primary key
 * - created_by_mandate_id / updated_by_mandate_id
 * - node_id / territory_id
 * - soft deletes (kecuali votes — immutable)
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. MEETING SESSIONS
        Schema::create('meeting_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('node_id');
            $table->string('territory_id', 20);
            $table->string('meeting_number', 100);
            $table->string('title', 255);
            $table->enum('meeting_type', [
                'pleno', 'rapat_kerja', 'rapat_koordinasi', 'rapat_darurat', 'khusus',
            ]);

            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->string('venue', 255)->nullable();
            $table->enum('venue_type', ['offline', 'online', 'hybrid'])->default('offline');

            $table->integer('quorum_required')->default(0);
            $table->boolean('quorum_met')->default(false);

            // Mandate-based roles
            $table->unsignedBigInteger('chairperson_mandate_id');
            $table->unsignedBigInteger('secretary_mandate_id');
            $table->unsignedBigInteger('approved_by_mandate_id')->nullable();

            // Status lifecycle
            $table->enum('status', [
                'draft', 'scheduled', 'invitation', 'running',
                'voting', 'decision', 'minutes', 'closed', 'cancelled',
            ])->default('draft');

            // Linkage (optional)
            $table->unsignedBigInteger('related_incident_id')->nullable();
            $table->uuid('related_letter_id')->nullable();

            // Audit governance fields
            $table->unsignedBigInteger('created_by_mandate_id');
            $table->unsignedBigInteger('updated_by_mandate_id')->nullable();

            // Immutability hash
            $table->string('document_hash', 64)->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('node_id')->references('id')->on('org_nodes')->onDelete('restrict');
            $table->foreign('chairperson_mandate_id', 'fk_ms_chairperson')
                  ->references('id')->on('org_mandates')->onDelete('restrict');
            $table->foreign('secretary_mandate_id', 'fk_ms_secretary')
                  ->references('id')->on('org_mandates')->onDelete('restrict');
            $table->foreign('approved_by_mandate_id', 'fk_ms_approver')
                  ->references('id')->on('org_mandates')->onDelete('set null');
            $table->foreign('created_by_mandate_id', 'fk_ms_created_by')
                  ->references('id')->on('org_mandates')->onDelete('restrict');
            $table->foreign('updated_by_mandate_id', 'fk_ms_updated_by')
                  ->references('id')->on('org_mandates')->onDelete('set null');
            $table->foreign('related_incident_id', 'fk_ms_incident')
                  ->references('id_insiden')->on('operasi_insiden')->onDelete('set null');

            // Indexes
            $table->index(['node_id', 'status'], 'idx_ms_node_status');
            $table->index(['territory_id', 'status'], 'idx_ms_territory_status');
            $table->index('scheduled_at', 'idx_ms_scheduled');
            $table->index('chairperson_mandate_id', 'idx_ms_chairperson');
        });

        // 2. MEETING AGENDAS
        Schema::create('meeting_agendas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('meeting_id');
            $table->integer('sequence');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->unsignedBigInteger('presenter_mandate_id')->nullable();
            $table->enum('status', ['pending', 'discussed', 'deferred', 'decided'])->default('pending');
            $table->text('resolution')->nullable();

            $table->unsignedBigInteger('node_id');
            $table->string('territory_id', 20);
            $table->unsignedBigInteger('created_by_mandate_id');
            $table->unsignedBigInteger('updated_by_mandate_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('meeting_id', 'fk_ma_meeting')
                  ->references('id')->on('meeting_sessions')->onDelete('cascade');
            $table->foreign('node_id', 'fk_ma_node')
                  ->references('id')->on('org_nodes')->onDelete('restrict');
            $table->foreign('presenter_mandate_id', 'fk_ma_presenter')
                  ->references('id')->on('org_mandates')->onDelete('set null');
            $table->foreign('created_by_mandate_id', 'fk_ma_created_by')
                  ->references('id')->on('org_mandates')->onDelete('restrict');

            $table->index(['meeting_id', 'sequence'], 'idx_ma_meeting_seq');
        });

        // 3. MEETING ATTENDEES
        Schema::create('meeting_attendees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('meeting_id');
            $table->unsignedBigInteger('mandate_id');
            $table->enum('role_in_meeting', [
                'chairperson', 'secretary', 'presenter', 'voter', 'observer',
            ])->default('voter');
            $table->enum('attendance_status', [
                'invited', 'confirmed', 'present', 'absent', 'excused',
            ])->default('invited');
            $table->boolean('has_voting_right')->default(false);
            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('checked_in_at')->nullable();

            $table->unsignedBigInteger('node_id');
            $table->string('territory_id', 20);
            $table->unsignedBigInteger('created_by_mandate_id');
            $table->unsignedBigInteger('updated_by_mandate_id')->nullable();

            $table->timestamps();

            $table->foreign('meeting_id', 'fk_matt_meeting')
                  ->references('id')->on('meeting_sessions')->onDelete('cascade');
            $table->foreign('mandate_id', 'fk_matt_mandate')
                  ->references('id')->on('org_mandates')->onDelete('restrict');
            $table->foreign('node_id', 'fk_matt_node')
                  ->references('id')->on('org_nodes')->onDelete('restrict');
            $table->foreign('created_by_mandate_id', 'fk_matt_created_by')
                  ->references('id')->on('org_mandates')->onDelete('restrict');

            $table->unique(['meeting_id', 'mandate_id'], 'uq_matt_meeting_mandate');
            $table->index(['mandate_id', 'attendance_status'], 'idx_matt_mandate_status');
        });

        // 4. MEETING VOTES (IMMUTABLE — no update, no soft delete)
        Schema::create('meeting_votes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('meeting_id');
            $table->uuid('agenda_id');
            $table->unsignedBigInteger('voter_mandate_id');
            $table->enum('vote', ['approve', 'reject', 'abstain']);
            $table->text('reason')->nullable();
            $table->dateTime('voted_at');

            // Immutable snapshot (tidak pernah berubah setelah di-insert)
            $table->string('voter_position_snapshot', 255);
            $table->string('voter_sk_snapshot', 100)->nullable();

            $table->unsignedBigInteger('node_id');
            $table->string('territory_id', 20);
            $table->unsignedBigInteger('created_by_mandate_id');

            $table->timestamp('created_at')->useCurrent();

            $table->foreign('meeting_id', 'fk_mv_meeting')
                  ->references('id')->on('meeting_sessions')->onDelete('restrict');
            $table->foreign('agenda_id', 'fk_mv_agenda')
                  ->references('id')->on('meeting_agendas')->onDelete('restrict');
            $table->foreign('voter_mandate_id', 'fk_mv_voter')
                  ->references('id')->on('org_mandates')->onDelete('restrict');
            $table->foreign('node_id', 'fk_mv_node')
                  ->references('id')->on('org_nodes')->onDelete('restrict');
            $table->foreign('created_by_mandate_id', 'fk_mv_created_by')
                  ->references('id')->on('org_mandates')->onDelete('restrict');

            $table->unique(['agenda_id', 'voter_mandate_id'], 'uq_mv_agenda_voter');
            $table->index('meeting_id', 'idx_mv_meeting');
        });

        // 5. MEETING MINUTES
        Schema::create('meeting_minutes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('meeting_id');
            $table->longText('content_snapshot');
            $table->text('summary')->nullable();

            // Sign-off chain (mandate-based)
            $table->unsignedBigInteger('prepared_by_mandate_id');
            $table->unsignedBigInteger('reviewed_by_mandate_id')->nullable();
            $table->unsignedBigInteger('approved_by_mandate_id')->nullable();

            $table->enum('status', ['draft', 'review', 'approved', 'locked'])->default('draft');
            $table->string('document_hash', 64)->nullable();
            $table->string('file_path', 255)->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('locked_at')->nullable();

            $table->unsignedBigInteger('node_id');
            $table->string('territory_id', 20);
            $table->unsignedBigInteger('created_by_mandate_id');
            $table->unsignedBigInteger('updated_by_mandate_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('meeting_id', 'fk_mm_meeting')
                  ->references('id')->on('meeting_sessions')->onDelete('cascade');
            $table->foreign('node_id', 'fk_mm_node')
                  ->references('id')->on('org_nodes')->onDelete('restrict');
            $table->foreign('prepared_by_mandate_id', 'fk_mm_prepared_by')
                  ->references('id')->on('org_mandates')->onDelete('restrict');
            $table->foreign('reviewed_by_mandate_id', 'fk_mm_reviewed_by')
                  ->references('id')->on('org_mandates')->onDelete('set null');
            $table->foreign('approved_by_mandate_id', 'fk_mm_approved_by')
                  ->references('id')->on('org_mandates')->onDelete('set null');
            $table->foreign('created_by_mandate_id', 'fk_mm_created_by')
                  ->references('id')->on('org_mandates')->onDelete('restrict');

            $table->index('meeting_id', 'idx_mm_meeting');
            $table->index('status', 'idx_mm_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_minutes');
        Schema::dropIfExists('meeting_votes');
        Schema::dropIfExists('meeting_attendees');
        Schema::dropIfExists('meeting_agendas');
        Schema::dropIfExists('meeting_sessions');
    }
};
