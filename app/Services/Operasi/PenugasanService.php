<?php

namespace App\Services\Operasi;

use App\Models\AuthUser;
use App\Models\OperasiInsiden;
use App\Models\OperasiKlaster;
use App\Models\OperasiPenugasan;
use App\Models\OperasiPenugasanHistory;
use App\Models\OperasiPosaju;
use App\Models\RelawanKebutuhan;
use App\Services\Auth\AuthorizationContextService;
use Exception;
use Illuminate\Support\Facades\DB;

class PenugasanService
{
    private AuthorizationContextService $authCtx;

    public function __construct(AuthorizationContextService $authCtx)
    {
        $this->authCtx = $authCtx;
    }

    public function createPenugasan(array $data): OperasiPenugasan
    {
        return DB::transaction(function () use ($data) {
            $user = $this->authCtx->getCurrentUser();
            if (!$user) {
                throw new Exception("User tidak terautentikasi.");
            }

            // Readiness check will be added here from VolunteerAvailabilityService

            // Validasi apakah pengguna sudah memiliki penugasan aktif (selain selesai/dibatalkan/rejected)
            $isAlreadyAssigned = OperasiPenugasan::where('id_insiden', $data['id_insiden'])
                ->where('id_pengguna', $data['id_pengguna'])
                ->whereNotIn('status_penugasan', ['completed', 'cancelled', 'rejected'])
                ->exists();

            if ($isAlreadyAssigned) {
                throw new Exception("Relawan sudah memiliki penugasan aktif di insiden ini.");
            }

            if ($data['peran_otoritas'] === 'trc') {
                $targetUser = AuthUser::find($data['id_pengguna']);
                if (!$targetUser) {
                    throw new Exception("User tidak ditemukan.");
                }
                if (!$targetUser->hasRole('trc')) {
                    throw new Exception("Penugasan TRC hanya bisa diberikan kepada user dengan peran TRC.");
                }
                $insiden = OperasiInsiden::findOrFail($data['id_insiden']);
                if ($targetUser->default_scope_type === 'pcnu' && $targetUser->default_scope_id !== $insiden->id_pcnu) {
                    throw new Exception("TRC hanya bisa ditugaskan kepada TRC PCNU setempat.");
                }
            }

            $penugasan = OperasiPenugasan::create([
                'id_insiden' => $data['id_insiden'],
                'id_pengguna' => $data['id_pengguna'],
                'id_klaster_operasi' => $data['id_klaster_operasi'] ?? null,
                'peran_otoritas' => $data['peran_otoritas'],
                'waktu_mulai' => $data['waktu_mulai'] ?? now(),
                'status_penugasan' => 'draft',
                'ditugaskan_oleh' => $user->id_pengguna,
                'catatan' => $data['catatan'] ?? null,
            ]);

            $this->recordHistory($penugasan, null, 'draft', $user->id_pengguna);

            return $penugasan;
        });
    }

    public function updateStatus(OperasiPenugasan $penugasan, string $status, ?string $catatan = null): OperasiPenugasan
    {
        $user = $this->authCtx->getCurrentUser();
        if (!$user) {
            throw new Exception("User tidak terautentikasi.");
        }

        $status = strtolower($status);
        $oldStatus = strtolower($penugasan->status_penugasan);

        $this->validateTransition($penugasan, $oldStatus, $status);

        if ($catatan) {
            $penugasan->catatan = $catatan;
        }

        if ($status === 'completed' || $status === 'selesai') {
            $penugasan->waktu_selesai = now();
        }

        $penugasan->status_penugasan = $status;
        $penugasan->save();

        $this->recordHistory($penugasan, $oldStatus, $status, $user->id_pengguna);

        if ($status === 'accepted' && in_array($penugasan->peran_otoritas, ['koordinator_pos', 'koordinator_klaster'])) {
            $this->bukaRekrutmenRelawan($penugasan);
        }

        return $penugasan;
    }

    private function validateTransition(OperasiPenugasan $penugasan, string $from, string $to): void
    {
        $validTransitions = [
            'draft'    => ['assigned', 'accepted', 'cancelled'],
            'assigned' => ['notified', 'cancelled'],
            'notified' => ['accepted', 'rejected', 'cancelled'],
            'accepted' => ['on_route', 'cancelled'],
            'on_route' => ['on_site', 'cancelled'],
            'on_site'  => ['completed'],
            'rejected' => [],
            'completed'=> [],
            'cancelled'=> [],
        ];

        // Also if we use old statuses 'aktif'
        if ($from === 'aktif' || $from === 'selesai' || $from === 'dibatalkan') {
            // handle legacy state mapping loosely or block
            if ($from === 'selesai' || $from === 'dibatalkan') {
                 throw new Exception("Tidak dapat mengubah status penugasan yang sudah selesai/dibatalkan.");
            }
        }

        if (isset($validTransitions[$from]) && !in_array($to, $validTransitions[$from])) {
            throw new Exception("Transisi status tidak valid dari $from ke $to.");
        }

        if ($from === 'draft' && $to === 'accepted' && !in_array($penugasan->peran_otoritas, ['koordinator_pos', 'koordinator_klaster'])) {
            throw new Exception("Hanya koordinator yang bisa menyetujui penugasan langsung dari draft.");
        }
    }

    private function bukaRekrutmenRelawan(OperasiPenugasan $penugasan): void
    {
        $insiden = OperasiInsiden::find($penugasan->id_insiden);
        if (!$insiden) return;

        if ($penugasan->peran_otoritas === 'koordinator_pos') {
            $posaju = OperasiPosaju::where('pj_posaju', $penugasan->id_pengguna)
                ->where('id_insiden', $penugasan->id_insiden)
                ->where('status_alur', 'aktif')
                ->first();

            if ($posaju) {
                RelawanKebutuhan::firstOrCreate(
                    [
                        'id_insiden' => $insiden->id_insiden,
                        'id_posaju' => $posaju->id_posaju,
                        'judul_posisi' => 'Relawan Pos Aju — ' . $posaju->nama_posaju,
                    ],
                    [
                        'deskripsi_tugas' => 'Membantu operasional Pos Aju ' . $posaju->nama_posaju,
                        'jumlah_dibutuhkan' => 10,
                        'status_rekrutmen' => 'dibuka',
                    ]
                );
            }
        }

        if ($penugasan->peran_otoritas === 'koordinator_klaster') {
            $klasters = OperasiKlaster::where('id_insiden', $penugasan->id_insiden)
                ->where('status_klaster', 'aktif')
                ->whereNull('waktu_ditutup')
                ->get();

            foreach ($klasters as $klaster) {
                $namaKlaster = $klaster->masterKlaster?->nama_klaster ?? 'Klaster #' . $klaster->id_klaster_operasi;
                RelawanKebutuhan::firstOrCreate(
                    [
                        'id_insiden' => $insiden->id_insiden,
                        'id_operasi_klaster' => $klaster->id_klaster_operasi,
                        'judul_posisi' => 'Relawan Klaster ' . $namaKlaster,
                    ],
                    [
                        'deskripsi_tugas' => 'Bergabung dalam tim klaster ' . $namaKlaster,
                        'jumlah_dibutuhkan' => 10,
                        'status_rekrutmen' => 'dibuka',
                    ]
                );
            }
        }
    }

    private function recordHistory(OperasiPenugasan $penugasan, ?string $oldStatus, string $newStatus, int $userId): void
    {
        OperasiPenugasanHistory::create([
            'id_penugasan' => $penugasan->getKey(),
            'status_sebelumnya' => $oldStatus,
            'status_baru' => $newStatus,
            'diubah_oleh' => $userId,
        ]);
    }
}
