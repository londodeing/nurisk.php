<?php

namespace App\Services\CommandCenter;

use App\Models\AuthUser;
use App\Models\OperasiPenugasan;
use App\Models\OperasiPosaju;
use App\Services\Auth\AuthorizationContextService;

class ContactDirectoryService
{
    public function __construct(
        private AuthorizationContextService $authCtx,
    ) {}

    public function getContacts(AuthUser $user): array
    {
        $role = $user->peran?->nama_peran;

        return match ($role) {
            'super_admin', 'pwnu' => $this->forPwnu(),
            'pcnu' => $this->forPcnu($user),
            'relawan' => $this->forRelawan($user),
            default => [],
        };
    }

    private function forPwnu(): array
    {
        return [];
    }

    private function forPcnu(AuthUser $user): array
    {
        $pcnuId = $this->authCtx->getScopeId();
        $contacts = [];

        $pjPosko = OperasiPosaju::whereHas('insiden', fn($q) => $q->where('id_pcnu', $pcnuId))
            ->whereNotNull('pj_posaju')
            ->with('pj.profil')
            ->get();

        foreach ($pjPosko as $posko) {
            if ($posko->pj && $posko->pj->profil) {
                $contacts[] = [
                    'nama' => $posko->pj->profil->nama_lengkap,
                    'jabatan' => 'PJ Posko ' . $posko->nama_posaju,
                    'unit' => 'Posko',
                    'no_hp' => $posko->pj->no_hp,
                    'color' => 'primary',
                ];
            }
        }

        return $contacts;
    }

    private function forRelawan(AuthUser $user): array
    {
        $contacts = [];

        $penugasan = OperasiPenugasan::where('id_pengguna', $user->id_pengguna)
            ->where('status_penugasan', 'aktif')
            ->with('insiden.posaju.pj.profil')
            ->first();

        if ($penugasan && $penugasan->insiden) {
            foreach ($penugasan->insiden->posaju as $posko) {
                if ($posko->pj && $posko->pj->profil) {
                    $contacts[] = [
                        'nama' => $posko->pj->profil->nama_lengkap,
                        'jabatan' => 'PJ Posko ' . $posko->nama_posaju,
                        'unit' => 'Supervisor',
                        'no_hp' => $posko->pj->no_hp,
                        'color' => 'success',
                    ];
                }
            }
        }

        $contacts[] = [
            'nama' => 'Call Center Darurat',
            'jabatan' => 'Emergency',
            'unit' => 'Nasional',
            'no_hp' => '119',
            'color' => 'danger',
        ];

        return $contacts;
    }
}
