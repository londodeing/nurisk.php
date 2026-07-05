<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Exception;

trait HasImmutableAudit
{
    /**
     * Memanggil Governance Audit Trail saat sebuah event strategis dilakukan (misal: approve_budget)
     */
    public function recordGovernanceAudit(array $snapshot, string $actionType)
    {
        DB::table('org_audit_logs')->insert([
            'actor_user_id' => $snapshot['actor_id'],
            'actor_name' => $snapshot['actor_name'],
            'actor_position' => $snapshot['actor_position'],
            'sk_number' => $snapshot['sk_number'],
            'mandate_id' => $snapshot['mandate_id'],
            'delegation_id' => $snapshot['delegation_id'],
            'action_type' => $actionType,
            'target_table' => $this->getTable(),
            'target_id' => $this->getKey(),
            'digital_signature' => hash('sha256', json_encode($snapshot) . $actionType . $this->getKey() . config('app.key')),
            'timestamp' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Helper metod untuk mengeksekusi aksi jika dan hanya jika validasi otoritas 8-Lapis sukses
     */
    public function executeWithGovernance(string $authorityCode, int $nodeId, callable $action)
    {
        $legalService = app(\App\Services\Governance\LegalValidationService::class);
        $user = auth()->user();

        if (!$user) {
            throw new Exception("Unauthorized. Tidak ada user yang login.");
        }

        // 1. Validasi legalitas otoritas via 8-Layer Abstraction
        $snapshot = $legalService->validateAndGetSnapshot($user, $authorityCode, $nodeId);

        DB::beginTransaction();
        try {
            // 2. Eksekusi aksi (save ke DB, ubah status surat, dll)
            $action($this);

            // 3. Catat ke Audit Trail secara immutable dengan Digital Signature
            $this->recordGovernanceAudit($snapshot, strtoupper($authorityCode));

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
