<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Structure Levels (e.g. PWNU, PCNU, MWCNU)
        Schema::create('org_structure_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique(); // e.g. 'PWNU'
            $table->integer('weight')->default(0); // 1 = PWNU, 2 = PCNU (smaller is higher in hierarchy)
            $table->timestamps();
        });

        // 2. Institutions (e.g. NU Peduli, LPBI, Lazisnu)
        Schema::create('org_institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique(); // 'NU Peduli'
            $table->string('domain', 50)->nullable(); // 'command', 'operations', 'finance'
            $table->timestamps();
        });

        // 3. Nodes (The Concrete Unit: Institution + Level + Territory)
        Schema::create('org_nodes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('institution_id');
            $table->unsignedBigInteger('structure_level_id');
            $table->string('territory_code', 20); // 33 for Jateng, 33.20 for Jepara
            $table->string('name', 255); // Auto generated: "LPBI PCNU Jepara"
            $table->string('status', 50)->default('active'); // active, inactive, dissolved
            $table->timestamps();

            $table->foreign('institution_id')->references('id')->on('org_institutions')->onDelete('cascade');
            $table->foreign('structure_level_id')->references('id')->on('org_structure_levels')->onDelete('cascade');
            $table->unique(['institution_id', 'structure_level_id', 'territory_code'], 'org_nodes_unique');
        });

        // 4. Positions (e.g. Ketua, Sekretaris, Bendahara)
        Schema::create('org_positions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); // 'Ketua'
            $table->integer('level')->default(0); // 1 = top tier
            $table->timestamps();
        });

        // 5. Node Positions (Pivot for Node and Position)
        Schema::create('org_node_positions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('node_id');
            $table->unsignedBigInteger('position_id');
            $table->timestamps();

            $table->foreign('node_id')->references('id')->on('org_nodes')->onDelete('cascade');
            $table->foreign('position_id')->references('id')->on('org_positions')->onDelete('cascade');
            $table->unique(['node_id', 'position_id']);
        });

        // 6. Governance Functions (e.g. Incident Commander)
        Schema::create('org_governance_functions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique(); // 'Incident Commander'
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });

        // 7. Position Functions (Pivot mapping Position -> Governance Function)
        Schema::create('org_position_functions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('node_position_id');
            $table->unsignedBigInteger('function_id');
            $table->timestamps();

            $table->foreign('node_position_id')->references('id')->on('org_node_positions')->onDelete('cascade');
            $table->foreign('function_id')->references('id')->on('org_governance_functions')->onDelete('cascade');
            $table->unique(['node_position_id', 'function_id']);
        });

        // 8. Authorities (e.g. activate_incident)
        Schema::create('org_authorities', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique(); // 'activate_incident'
            $table->string('domain', 50); // operational, financial, governance, info
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });

        // 9. Function Authorities (Pivot mapping Governance Function -> Authority)
        Schema::create('org_function_authorities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('function_id');
            $table->unsignedBigInteger('authority_id');
            $table->timestamps();

            $table->foreign('function_id')->references('id')->on('org_governance_functions')->onDelete('cascade');
            $table->foreign('authority_id')->references('id')->on('org_authorities')->onDelete('cascade');
            $table->unique(['function_id', 'authority_id']);
        });

        // 10. SKs
        Schema::create('org_sks', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_sk', 100)->unique();
            $table->date('tanggal_mulai');
            $table->date('tanggal_berakhir')->nullable();
            $table->string('status', 50)->default('draft'); // draft, review, approved, active, expired, revoked
            $table->timestamps();
        });

        // 11. Mandates
        Schema::create('org_mandates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sk_id')->nullable();
            $table->unsignedBigInteger('user_id'); // mengacu ke auth_users
            $table->unsignedBigInteger('node_position_id');
            $table->date('tanggal_mulai');
            $table->date('tanggal_berakhir')->nullable();
            $table->string('status', 50)->default('active'); // draft, active, expired, terminated
            $table->timestamps();

            $table->foreign('sk_id')->references('id')->on('org_sks')->onDelete('set null');
            $table->foreign('user_id')->references('id_pengguna')->on('auth_users')->onDelete('cascade');
            $table->foreign('node_position_id')->references('id')->on('org_node_positions')->onDelete('cascade');
        });

        // 12. Delegations
        Schema::create('org_delegations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mandat_asal_id');
            $table->unsignedBigInteger('mandat_pengganti_id');
            $table->dateTime('mulai');
            $table->dateTime('selesai');
            $table->string('jenis', 50)->default('full'); // full, partial
            $table->timestamps();

            $table->foreign('mandat_asal_id')->references('id')->on('org_mandates')->onDelete('cascade');
            $table->foreign('mandat_pengganti_id')->references('id')->on('org_mandates')->onDelete('cascade');
        });

        // 13. Audit Trail
        Schema::create('org_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('timestamp')->useCurrent();
            $table->unsignedBigInteger('actor_user_id');
            $table->string('actor_name');
            $table->string('actor_position');
            $table->string('sk_number')->nullable();
            $table->unsignedBigInteger('mandate_id')->nullable();
            $table->unsignedBigInteger('delegation_id')->nullable();
            $table->string('action_type');
            $table->string('target_table')->nullable();
            $table->string('target_id')->nullable();
            $table->text('digital_signature')->nullable();
            $table->timestamps();

            $table->foreign('actor_user_id')->references('id_pengguna')->on('auth_users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('org_audit_logs');
        Schema::dropIfExists('org_delegations');
        Schema::dropIfExists('org_mandates');
        Schema::dropIfExists('org_sks');
        Schema::dropIfExists('org_function_authorities');
        Schema::dropIfExists('org_authorities');
        Schema::dropIfExists('org_position_functions');
        Schema::dropIfExists('org_governance_functions');
        Schema::dropIfExists('org_node_positions');
        Schema::dropIfExists('org_positions');
        Schema::dropIfExists('org_nodes');
        Schema::dropIfExists('org_institutions');
        Schema::dropIfExists('org_structure_levels');
    }
};
