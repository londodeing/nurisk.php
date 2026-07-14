<?php

namespace App\Services\Governance;

use App\Models\AuthUser;
use Illuminate\Support\Facades\DB;
use Exception;

class LegalValidationService
{
    /**
     * Algoritma Validasi Otoritas 8-Lapis (Mandate-Based Authority)
     * 
     * @param AuthUser $user Pengguna yang meminta aksi
     * @param string $authorityCode Kode wewenang (mis: 'activate_incident')
     * @param int $nodeId ID Concrete Node (mis: LPBI PCNU Jepara)
     * @return array Snapshot data legal untuk direkam ke Audit Trail
     * @throws Exception Jika validasi gagal
     */
    public function validateAndGetSnapshot(AuthUser $user, string $authorityCode, int $nodeId): array
    {
        // 1. Lapis Identitas: User is active?
        if ($user->status_akun !== AuthUser::STATUS_ACTIVE) {
            throw new Exception("Akun tidak aktif.");
        }

        // 2. Traversal 8 Lapis (User -> Mandate -> NodePosition -> Function -> Authority)
        $mandate = DB::table('org_mandates')
            ->join('org_sks', 'org_mandates.sk_id', '=', 'org_sks.id')
            ->join('org_node_positions', 'org_mandates.node_position_id', '=', 'org_node_positions.id')
            ->join('org_positions', 'org_node_positions.position_id', '=', 'org_positions.id')
            ->join('org_nodes', 'org_node_positions.node_id', '=', 'org_nodes.id')
            ->join('org_position_functions', 'org_node_positions.id', '=', 'org_position_functions.node_position_id')
            ->join('org_governance_functions', 'org_position_functions.function_id', '=', 'org_governance_functions.id')
            ->join('org_function_authorities', 'org_governance_functions.id', '=', 'org_function_authorities.function_id')
            ->join('org_authorities', 'org_function_authorities.authority_id', '=', 'org_authorities.id')
            ->where('org_mandates.user_id', $user->id_pengguna)
            ->where('org_nodes.id', $nodeId) // Lapis Teritori & Institusi
            ->where('org_mandates.status', 'active') // Mandat Aktif
            ->where('org_mandates.tanggal_mulai', '<=', today())
            ->where(function ($query) {
                $query->whereNull('org_mandates.tanggal_berakhir')
                      ->orWhere('org_mandates.tanggal_berakhir', '>=', today());
            })
            ->where('org_sks.status', 'active') // SK Aktif
            ->where('org_authorities.code', $authorityCode) // Otoritas Tepat
            ->select(
                'org_mandates.id as mandat_id',
                'org_sks.nomor_sk as sk_snapshot',
                'org_positions.name as position_name',
                'org_nodes.name as node_name'
            )
            ->first();

        if ($mandate) {
            return [
                'actor_id' => $user->id_pengguna,
                'actor_name' => $user->profil?->nama_lengkap ?? 'Unknown',
                'actor_position' => $mandate->position_name . ' ' . $mandate->node_name,
                'sk_number' => $mandate->sk_snapshot,
                'mandate_id' => $mandate->mandat_id,
                'delegation_id' => null,
            ];
        }

        // 3. Fallback ke Delegasi (PLT)
        $delegation = DB::table('org_delegations')
            ->join('org_mandates as m_pengganti', 'org_delegations.mandat_pengganti_id', '=', 'm_pengganti.id')
            ->join('org_mandates as m_asal', 'org_delegations.mandat_asal_id', '=', 'm_asal.id')
            ->join('org_sks', 'm_asal.sk_id', '=', 'org_sks.id')
            ->join('org_node_positions', 'm_asal.node_position_id', '=', 'org_node_positions.id')
            ->join('org_positions', 'org_node_positions.position_id', '=', 'org_positions.id')
            ->join('org_nodes', 'org_node_positions.node_id', '=', 'org_nodes.id')
            ->join('org_position_functions', 'org_node_positions.id', '=', 'org_position_functions.node_position_id')
            ->join('org_governance_functions', 'org_position_functions.function_id', '=', 'org_governance_functions.id')
            ->join('org_function_authorities', 'org_governance_functions.id', '=', 'org_function_authorities.function_id')
            ->join('org_authorities', 'org_function_authorities.authority_id', '=', 'org_authorities.id')
            ->where('m_pengganti.user_id', $user->id_pengguna)
            ->where('org_nodes.id', $nodeId)
            ->where('org_delegations.mulai', '<=', now())
            ->where('org_delegations.selesai', '>=', now())
            ->where('m_asal.status', 'active') 
            ->where('org_sks.status', 'active')
            ->where('org_authorities.code', $authorityCode)
            ->select(
                'org_delegations.id as delegasi_id',
                'm_asal.id as mandat_id',
                'org_sks.nomor_sk as sk_snapshot',
                'org_positions.name as position_name',
                'org_nodes.name as node_name'
            )
            ->first();

        if ($delegation) {
            return [
                'actor_id' => $user->id_pengguna,
                'actor_name' => $user->profil?->nama_lengkap ?? 'Unknown',
                'actor_position' => $delegation->position_name . ' ' . $delegation->node_name . ' (A.N. Delegasi)',
                'sk_number' => $delegation->sk_snapshot,
                'mandate_id' => $delegation->mandat_id,
                'delegation_id' => $delegation->delegasi_id,
            ];
        }

        throw new Exception("Akses ditolak: Anda tidak memiliki Mandat aktif pada Node ini untuk otoritas [{$authorityCode}].");
    }
}
