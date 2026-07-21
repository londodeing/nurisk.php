<?php

namespace App\Services\Operasi;

use App\Models\AssessmentUtama;
use App\Models\OperasiInsiden;
use App\Models\AuthUser;
use App\Models\Assessment\AssessmentLokasiDetail;
use App\Models\Assessment\AssessmentNarasiDetail;
use App\Models\Assessment\AssessmentKebutuhanLanjutan;
use App\Models\Assessment\AssessmentKebutuhanNumerik;
use App\Models\Assessment\AssessmentKebutuhanNumerikMaster;
use App\Models\Assessment\AssessmentDampakManusiaV2;
use App\Models\Assessment\AssessmentDampakRumah;
use App\Models\Assessment\AssessmentDampakFasum;
use App\Models\Assessment\AssessmentDampakVital;
use Illuminate\Support\Facades\DB;

class AssessmentService
{
    public function __construct(
        private \App\Services\InsidenService $insidenService
    ) {}

    /**
     * Create a new Assessment atomically (Old flow).
     */
    private function getPreviousAssessment(int $idInsiden): ?AssessmentUtama
    {
        return AssessmentUtama::with(['dampakManusiaV2', 'dampakManusia', 'dampakRumah', 'dampakFasum', 'dampakVital', 'dampakLingkungan', 'dampakEkonomi'])
            ->where('id_insiden', $idInsiden)
            ->orderBy('id_assessment_utama', 'desc')
            ->first();
    }

    public function createAssessment(array $data): AssessmentUtama
    {
        $insiden = OperasiInsiden::findOrFail($data['id_insiden']);

        // BR-ASSESSMENT-001
        if (!in_array($insiden->status_insiden, ['terverifikasi', 'respon'])) {
            throw new \InvalidArgumentException("Assessment hanya dapat dibuat untuk insiden yang sudah terverifikasi atau dalam tahap respon.");
        }

        return DB::transaction(function () use ($data) {
            $clean = fn (array $arr) => array_filter($arr, fn ($v) => $v !== null && $v !== '');
            $prev = $this->getPreviousAssessment($data['id_insiden']);

            $utama = AssessmentUtama::create([
                'id_insiden'               => $data['id_insiden'],
                'id_petugas_assessment'    => $data['id_petugas_assessment'] ?? auth()->id() ?? 1,
                'jenis_laporan'            => $data['jenis_laporan'],
                'cakupan_wilayah_deskripsi'=> $data['cakupan_wilayah_deskripsi'],
                'latitude'                 => $data['latitude'] ?? null,
                'longitude'                => $data['longitude'] ?? null,
                'waktu_assesment'          => $data['waktu_assesment'],
                'is_latest'                => true,
            ]);

            $id = $utama->id_assessment_utama;

            // ─── Dampak Manusia (V2 canonical table) ──────────────────────
            $dm = $data['dampak_manusia'] ?? [];
            $prevDm = $prev?->dampakManusiaV2;
            $prevMeninggal = $prevDm ? $prevDm->meninggal : ($prev?->dampakManusia?->meninggal ?? 0);
            
            $meninggal = isset($dm['meninggal']) ? (int)$dm['meninggal'] : ($prevDm?->meninggal ?? 0);
            $meninggal = max($meninggal, $prevMeninggal);

            AssessmentDampakManusiaV2::create([
                'id_assessment'        => $id,
                'meninggal'            => $meninggal,
                'hilang'               => isset($dm['hilang']) ? (int)$dm['hilang'] : ($prevDm?->hilang ?? 0),
                'luka_berat'           => isset($dm['luka_berat']) ? (int)$dm['luka_berat'] : ($prevDm?->luka_berat ?? 0),
                'luka_ringan'          => isset($dm['luka_ringan']) ? (int)$dm['luka_ringan'] : ($prevDm?->luka_ringan ?? 0),
                'terdampak_jiwa'       => isset($dm['menderita_mengungsi']) ? (int)$dm['menderita_mengungsi'] : (isset($dm['dampak_manusia']) ? (int)$dm['dampak_manusia'] : ($prevDm?->terdampak_jiwa ?? 0)),
                'terdampak_kk'         => isset($dm['terdampak_kk']) ? (int)$dm['terdampak_kk'] : ($prevDm?->terdampak_kk ?? 0),
                'pengungsi_jiwa'       => isset($dm['pengungsi_jiwa']) ? (int)$dm['pengungsi_jiwa'] : ($prevDm?->pengungsi_jiwa ?? 0),
                'pengungsi_kk'         => isset($dm['pengungsi_kk']) ? (int)$dm['pengungsi_kk'] : ($prevDm?->pengungsi_kk ?? 0),
                'pengungsi_balita'     => isset($dm['pengungsi_balita']) ? (int)$dm['pengungsi_balita'] : ($prevDm?->pengungsi_balita ?? 0),
                'pengungsi_lansia'     => isset($dm['pengungsi_lansia']) ? (int)$dm['pengungsi_lansia'] : ($prevDm?->pengungsi_lansia ?? 0),
                'pengungsi_disabilitas'=> isset($dm['pengungsi_disabilitas']) ? (int)$dm['pengungsi_disabilitas'] : ($prevDm?->pengungsi_disabilitas ?? 0),
                'pengungsi_ibu_hamil'  => isset($dm['pengungsi_ibu_hamil']) ? (int)$dm['pengungsi_ibu_hamil'] : ($prevDm?->pengungsi_ibu_hamil ?? 0),
            ]);

            // ─── Kebutuhan Mendesak (V1) & Kebutuhan Numerik (V2) ──────────
            if (isset($data['kebutuhan_mendesak']) && is_array($data['kebutuhan_mendesak'])) {
                $semuaMaster = AssessmentKebutuhanNumerikMaster::where('aktif', 1)->get();
                foreach ($data['kebutuhan_mendesak'] as $kebutuhan) {
                    $utama->kebutuhanMendesak()->create($kebutuhan);

                    // Terjemahkan ke V2 (Numerik)
                    $nama = strtolower(trim($kebutuhan['nama_kebutuhan']));
                    $match = $semuaMaster->first(function ($m) use ($nama) {
                        return strtolower(trim($m->nama_item)) === $nama || strtolower(trim($m->kode_item)) === str_replace(' ', '_', $nama);
                    });

                    if ($match && isset($kebutuhan['jumlah']) && (float)$kebutuhan['jumlah'] > 0) {
                        AssessmentKebutuhanNumerik::create([
                            'id_assessment'     => $id,
                            'id_item'           => $match->id_item,
                            'jumlah_dibutuhkan' => (float) $kebutuhan['jumlah'],
                            'satuan'            => $kebutuhan['satuan'] ?? $match->satuan_default,
                        ]);
                    }
                }
            }

            // ─── Dampak Infrastruktur V1 + V2 ─────────────────────────────
            $di = $data['dampak_infrastruktur'] ?? [];
            if (isset($data['dampak_infrastruktur'])) {
                $utama->dampakInfrastruktur()->create($di);
            }

            $prevDr = $prev?->dampakRumah;
            AssessmentDampakRumah::create([
                'id_assessment' => $id,
                'rusak_berat'   => isset($di['rumah_rusak_berat']) ? (int)$di['rumah_rusak_berat'] : ($prevDr?->rusak_berat ?? 0),
                'rusak_sedang'  => isset($di['rumah_rusak_sedang']) ? (int)$di['rumah_rusak_sedang'] : ($prevDr?->rusak_sedang ?? 0),
                'rusak_ringan'  => isset($di['rumah_rusak_ringan']) ? (int)$di['rumah_rusak_ringan'] : ($prevDr?->rusak_ringan ?? 0),
                'terendam'      => isset($di['rumah_terendam']) ? (int)$di['rumah_terendam'] : ($prevDr?->terendam ?? 0),
                'terancam'      => isset($di['rumah_terancam']) ? (int)$di['rumah_terancam'] : ($prevDr?->terancam ?? 0),
            ]);

            $prevDf = $prev?->dampakFasum;
            AssessmentDampakFasum::create([
                'id_assessment' => $id,
                'pendidikan'    => isset($di['fasilitas_pendidikan_rusak']) ? (int)$di['fasilitas_pendidikan_rusak'] : ($prevDf?->pendidikan ?? 0),
                'kesehatan'     => isset($di['fasilitas_kesehatan_rusak']) ? (int)$di['fasilitas_kesehatan_rusak'] : ($prevDf?->kesehatan ?? 0),
                'ibadah'        => isset($di['tempat_ibadah_rusak']) ? (int)$di['tempat_ibadah_rusak'] : ($prevDf?->ibadah ?? 0),
                'kantor'        => isset($di['kantor_pemerintah_rusak']) ? (int)$di['kantor_pemerintah_rusak'] : ($prevDf?->kantor ?? 0),
                'jembatan'      => isset($di['jembatan_putus']) || isset($di['jembatan_rusak']) ? ((int)($di['jembatan_putus'] ?? 0) + (int)($di['jembatan_rusak'] ?? 0)) : ($prevDf?->jembatan ?? 0),
                'listrik'       => isset($di['jaringan_listrik_padam_kk']) ? (int)$di['jaringan_listrik_padam_kk'] : ($prevDf?->listrik ?? 0),
                'komunikasi'    => isset($di['jaringan_komunikasi_putus']) ? (int)$di['jaringan_komunikasi_putus'] : ($prevDf?->komunikasi ?? 0),
                'sanitasi'      => isset($di['sanitasi']) ? (int)$di['sanitasi'] : ($prevDf?->sanitasi ?? 0),
                'pasar'         => isset($di['pasar']) ? (int)$di['pasar'] : ($prevDf?->pasar ?? 0),
                'spbu'          => isset($di['spbu']) ? (int)$di['spbu'] : ($prevDf?->spbu ?? 0),
                'catatan_fasum' => isset($di['catatan_infrastruktur']) ? $di['catatan_infrastruktur'] : ($prevDf?->catatan_fasum ?? null),
            ]);

            $prevDv = $prev?->dampakVital;
            AssessmentDampakVital::create([
                'id_assessment'       => $id,
                'jalan'               => isset($di['jalan_rusak_km']) ? (float)$di['jalan_rusak_km'] : ($prevDv?->jalan ?? 0),
                'air_bersih'          => isset($di['sarana_air_bersih_rusak']) ? (int)$di['sarana_air_bersih_rusak'] : ($prevDv?->air_bersih ?? 0),
                'listrik'             => isset($di['jaringan_listrik_padam_kk']) ? (int)$di['jaringan_listrik_padam_kk'] : ($prevDv?->listrik ?? 0),
                'telekomunikasi'      => isset($di['jaringan_komunikasi_putus']) ? (int)$di['jaringan_komunikasi_putus'] : ($prevDv?->telekomunikasi ?? 0),
                'sumber_air_tercemar' => isset($data['dampak_lingkungan']['sumber_air_tercemar']) ? (bool)$data['dampak_lingkungan']['sumber_air_tercemar'] : ($prevDv?->sumber_air_tercemar ?? false),
                'irigasi'             => isset($di['irigasi']) ? (float)$di['irigasi'] : ($prevDv?->irigasi ?? 0),
                'sawah_ha'            => isset($di['sawah_ha']) ? (float)$di['sawah_ha'] : ($prevDv?->sawah_ha ?? 0),
                'ternak_ekor'         => isset($di['ternak_ekor']) ? (int)$di['ternak_ekor'] : ($prevDv?->ternak_ekor ?? 0),
                'hutan_ha'            => isset($di['hutan_ha']) ? (float)$di['hutan_ha'] : ($prevDv?->hutan_ha ?? 0),
                'spbu'                => isset($di['spbu']) ? (int)$di['spbu'] : ($prevDv?->spbu ?? 0),
                'catatan_vital'       => isset($di['catatan_infrastruktur']) ? $di['catatan_infrastruktur'] : ($prevDv?->catatan_vital ?? null),
            ]);

            // ─── Dampak Lingkungan (V1) ────────────────────────────────────
            $dl = $data['dampak_lingkungan'] ?? [];
            if (!empty($dl) || $prev?->dampakLingkungan) {
                $prevDl = $prev?->dampakLingkungan;
                $utama->dampakLingkungan()->create([
                    'lahan_pertanian_rusak_ha' => isset($dl['lahan_pertanian_rusak_ha']) ? (float)$dl['lahan_pertanian_rusak_ha'] : ($prevDl?->lahan_pertanian_rusak_ha ?? 0),
                    'hutan_terdampak_ha' => isset($dl['hutan_terdampak_ha']) ? (float)$dl['hutan_terdampak_ha'] : ($prevDl?->hutan_terdampak_ha ?? 0),
                    'lahan_tercemar_ha' => isset($dl['lahan_tercemar_ha']) ? (float)$dl['lahan_tercemar_ha'] : ($prevDl?->lahan_tercemar_ha ?? 0),
                    'sumber_air_tercemar' => isset($dl['sumber_air_tercemar']) ? (bool)$dl['sumber_air_tercemar'] : ($prevDl?->sumber_air_tercemar ?? false),
                    'pencemaran_tanah' => isset($dl['pencemaran_tanah']) ? (bool)$dl['pencemaran_tanah'] : ($prevDl?->pencemaran_tanah ?? false),
                    'erosi_sedimentasi' => isset($dl['erosi_sedimentasi']) ? (bool)$dl['erosi_sedimentasi'] : ($prevDl?->erosi_sedimentasi ?? false),
                    'kerusakan_ekosistem_pesisir' => isset($dl['kerusakan_pesisir']) ? (bool)$dl['kerusakan_pesisir'] : (isset($dl['kerusakan_ekosistem_pesisir']) ? (bool)$dl['kerusakan_ekosistem_pesisir'] : ($prevDl?->kerusakan_ekosistem_pesisir ?? false)),
                    'kerusakan_daerah_aliran_sungai' => isset($dl['kerusakan_daerah_aliran_sungai']) ? (bool)$dl['kerusakan_daerah_aliran_sungai'] : ($prevDl?->kerusakan_daerah_aliran_sungai ?? false),
                    'ternak_unggas_ekor' => isset($dl['unggas']) ? (int)$dl['unggas'] : ($prevDl?->ternak_unggas_ekor ?? 0),
                    'ternak_kaki_empat_ekor' => isset($dl['kaki_empat']) ? (int)$dl['kaki_empat'] : ($prevDl?->ternak_kaki_empat_ekor ?? 0),
                    'perikanan_kolam_ha' => isset($dl['perikanan_kolam']) ? (float)$dl['perikanan_kolam'] : (isset($dl['perikanan_kolam_ha']) ? (float)$dl['perikanan_kolam_ha'] : ($prevDl?->perikanan_kolam_ha ?? 0)),
                    'perikanan_nelayan_unit' => isset($dl['perikanan_nelayan']) ? (int)$dl['perikanan_nelayan'] : (isset($dl['perikanan_nelayan_unit']) ? (int)$dl['perikanan_nelayan_unit'] : ($prevDl?->perikanan_nelayan_unit ?? 0)),
                    'catatan_lingkungan' => isset($dl['catatan_lingkungan']) ? $dl['catatan_lingkungan'] : ($prevDl?->catatan_lingkungan ?? null),
                ]);
            }

            // ─── Dampak Ekonomi (V1) ───────────────────────────────────────
            $de = $data['dampak_ekonomi'] ?? [];
            if (!empty($de) || $prev?->dampakEkonomi) {
                $prevDe = $prev?->dampakEkonomi;
                $utama->dampakEkonomi()->create([
                    'persentase_ekonomi_terdampak' => isset($de['persentase']) ? $de['persentase'] : (isset($de['persentase_ekonomi_terdampak']) ? $de['persentase_ekonomi_terdampak'] : ($prevDe?->persentase_ekonomi_terdampak ?? null)),
                    'sektor_pencaharian_1' => isset($de['sektor_1']) ? $de['sektor_1'] : (isset($de['sektor_pencaharian_1']) ? $de['sektor_pencaharian_1'] : ($prevDe?->sektor_pencaharian_1 ?? null)),
                    'kontribusi_1' => isset($de['kontribusi_1']) ? (float)$de['kontribusi_1'] : ($prevDe?->kontribusi_1 ?? null),
                    'status_terdampak_1' => isset($de['status_1']) ? $de['status_1'] : (isset($de['status_terdampak_1']) ? $de['status_terdampak_1'] : ($prevDe?->status_terdampak_1 ?? null)),
                    'sektor_pencaharian_2' => isset($de['sektor_2']) ? $de['sektor_2'] : (isset($de['sektor_pencaharian_2']) ? $de['sektor_pencaharian_2'] : ($prevDe?->sektor_pencaharian_2 ?? null)),
                    'kontribusi_2' => isset($de['kontribusi_2']) ? (float)$de['kontribusi_2'] : ($prevDe?->kontribusi_2 ?? null),
                    'status_terdampak_2' => isset($de['status_2']) ? $de['status_2'] : (isset($de['status_terdampak_2']) ? $de['status_terdampak_2'] : ($prevDe?->status_terdampak_2 ?? null)),
                    'sektor_pencaharian_3' => isset($de['sektor_3']) ? $de['sektor_3'] : (isset($de['sektor_pencaharian_3']) ? $de['sektor_pencaharian_3'] : ($prevDe?->sektor_pencaharian_3 ?? null)),
                    'kontribusi_3' => isset($de['kontribusi_3']) ? (float)$de['kontribusi_3'] : ($prevDe?->kontribusi_3 ?? null),
                    'status_terdampak_3' => isset($de['status_3']) ? $de['status_3'] : (isset($de['status_terdampak_3']) ? $de['status_terdampak_3'] : ($prevDe?->status_terdampak_3 ?? null)),
                    'distribusi_hasil_panen' => isset($de['distribusi']) ? $de['distribusi'] : (isset($de['distribusi_hasil_panen']) ? $de['distribusi_hasil_panen'] : ($prevDe?->distribusi_hasil_panen ?? null)),
                    'fasilitas_pengolahan_kolektif' => isset($de['fasilitas']) ? $de['fasilitas'] : (isset($de['fasilitas_pengolahan_kolektif']) ? $de['fasilitas_pengolahan_kolektif'] : ($prevDe?->fasilitas_pengolahan_kolektif ?? null)),
                ]);
            }

            // ─── Biodata Kejadian (V1) & Lokasi Detail (V2) ────────────────
            if (isset($data['biodata_kejadian'])) {
                $utama->biodataKejadian()->create($clean($data['biodata_kejadian']));
            }
            AssessmentLokasiDetail::create([
                'id_assessment'    => $id,
                'alamat_spesifik'  => $data['cakupan_wilayah_deskripsi'] ?? null,
                'id_kec'           => $data['lokasi_detail']['id_kec'] ?? null,
                'id_desa'          => $data['lokasi_detail']['id_desa'] ?? null,
                'region_terdampak' => $data['lokasi_detail']['region_terdampak'] ?? null,
            ]);

            // ─── Narasi Kejadian (V1) & Narasi Detail (V2) ─────────────────
            if (isset($data['narasi_kejadian'])) {
                $utama->narasiKejadian()->create($clean($data['narasi_kejadian']));
            }
            AssessmentNarasiDetail::create([
                'id_assessment'    => $id,
                'kondisi_umum'     => $data['narasi_detail']['kondisi_umum'] ?? ($data['narasi_kejadian']['isi_narasi'] ?? null),
                'sebaran_dampak'   => $data['narasi_detail']['sebaran_dampak'] ?? null,
                'upaya_penanganan' => $data['narasi_detail']['upaya_penanganan'] ?? null,
                'kendala_lapangan' => $data['narasi_detail']['kendala_lapangan'] ?? null,
                'kendala_tambahan' => $data['narasi_detail']['kendala_tambahan'] ?? null,
                'rekomendasi_aksi' => $data['narasi_detail']['rekomendasi_aksi'] ?? null,
            ]);

            // ─── Kebutuhan Numerik & Lanjutan (V2) ──────────────────────────
            if (isset($data['kebutuhan_numerik']) && is_array($data['kebutuhan_numerik'])) {
                foreach ($data['kebutuhan_numerik'] as $kebNum) {
                    $utama->kebutuhanNumerik()->create($kebNum);
                }
            }

            if (isset($data['kebutuhan_lanjutan']) && is_array($data['kebutuhan_lanjutan'])) {
                $utama->kebutuhanLanjutan()->create($data['kebutuhan_lanjutan']);
            }

            return $utama->load([
                'dampakManusiaV2', 'kebutuhanNumerik', 'lokasiDetail', 'narasiDetail',
                'dampakInfrastruktur', 'dampakRumah', 'dampakFasum', 'dampakVital',
                'dampakLingkungan', 'dampakEkonomi',
                'biodataKejadian', 'narasiKejadian',
            ]);
        });
    }

    /**
     * Update an existing Assessment atomically (Old flow).
     */
    public function updateAssessment(AssessmentUtama $assessment, array $data): AssessmentUtama
    {
        return DB::transaction(function () use ($assessment, $data) {
            $clean = fn (array $arr) => array_filter($arr, fn ($v) => $v !== null && $v !== '');
            $assessment->update([
                'jenis_laporan'            => $data['jenis_laporan'] ?? $assessment->jenis_laporan,
                'cakupan_wilayah_deskripsi'=> $data['cakupan_wilayah_deskripsi'] ?? $assessment->cakupan_wilayah_deskripsi,
                'latitude'                 => array_key_exists('latitude', $data) ? $data['latitude'] : $assessment->latitude,
                'longitude'                => array_key_exists('longitude', $data) ? $data['longitude'] : $assessment->longitude,
                'waktu_assesment'          => $data['waktu_assesment'] ?? $assessment->waktu_assesment,
            ]);

            $id = $assessment->id_assessment_utama;

            // ─── Dampak Manusia (V2 canonical) ────────────────────────────
            if (isset($data['dampak_manusia'])) {
                $dm = $data['dampak_manusia'];

                $assessment->dampakManusiaV2()->updateOrCreate(['id_assessment' => $id], [
                    'meninggal'            => (int)($dm['meninggal'] ?? 0),
                    'hilang'               => (int)($dm['hilang'] ?? 0),
                    'luka_berat'           => (int)($dm['luka_berat'] ?? 0),
                    'luka_ringan'          => (int)($dm['luka_ringan'] ?? 0),
                    'terdampak_jiwa'       => (int)($dm['menderita_mengungsi'] ?? $dm['dampak_manusia'] ?? 0),
                    'terdampak_kk'         => (int)($dm['terdampak_kk'] ?? 0),
                    'pengungsi_jiwa'       => (int)($dm['pengungsi_jiwa'] ?? 0),
                    'pengungsi_kk'         => (int)($dm['pengungsi_kk'] ?? 0),
                    'pengungsi_balita'     => (int)($dm['pengungsi_balita'] ?? 0),
                    'pengungsi_lansia'     => (int)($dm['pengungsi_lansia'] ?? 0),
                    'pengungsi_disabilitas'=> (int)($dm['pengungsi_disabilitas'] ?? 0),
                    'pengungsi_ibu_hamil'  => (int)($dm['pengungsi_ibu_hamil'] ?? 0),
                ]);
            }

            // ─── Kebutuhan Numerik (V2) & Lanjutan (V2) ─────────────────────
            if (isset($data['kebutuhan_numerik']) && is_array($data['kebutuhan_numerik'])) {
                $assessment->kebutuhanNumerik()->delete();
                foreach ($data['kebutuhan_numerik'] as $kebNum) {
                    $assessment->kebutuhanNumerik()->create($kebNum);
                }
            }

            if (isset($data['kebutuhan_lanjutan']) && is_array($data['kebutuhan_lanjutan'])) {
                $assessment->kebutuhanLanjutan()->updateOrCreate(
                    ['id_assessment' => $id],
                    $data['kebutuhan_lanjutan']
                );
            }

            // ─── Dampak Infrastruktur V1+V2 ────────────────────────────────
            if (isset($data['dampak_infrastruktur'])) {
                $di = $data['dampak_infrastruktur'];
                if ($assessment->dampakInfrastruktur) {
                    $assessment->dampakInfrastruktur->update($di);
                } else {
                    $assessment->dampakInfrastruktur()->create($di);
                }

                $assessment->dampakRumah()->updateOrCreate(['id_assessment' => $id], [
                    'rusak_berat'  => (int)($di['rumah_rusak_berat'] ?? 0),
                    'rusak_sedang' => (int)($di['rumah_rusak_sedang'] ?? 0),
                    'rusak_ringan' => (int)($di['rumah_rusak_ringan'] ?? 0),
                    'terendam'     => (int)($di['rumah_terendam'] ?? 0),
                    'terancam'     => (int)($di['rumah_terancam'] ?? 0),
                ]);

                $assessment->dampakFasum()->updateOrCreate(['id_assessment' => $id], [
                    'pendidikan' => (int)($di['fasilitas_pendidikan_rusak'] ?? 0),
                    'kesehatan'  => (int)($di['fasilitas_kesehatan_rusak'] ?? 0),
                    'ibadah'     => (int)($di['tempat_ibadah_rusak'] ?? 0),
                    'kantor'     => (int)($di['kantor_pemerintah_rusak'] ?? 0),
                    'jembatan'   => (int)($di['jembatan_putus'] ?? 0) + (int)($di['jembatan_rusak'] ?? 0),
                    'listrik'    => (int)($di['jaringan_listrik_padam_kk'] ?? 0),
                    'komunikasi' => isset($di['jaringan_komunikasi_putus']) ? (int)$di['jaringan_komunikasi_putus'] : 0,
                    'sanitasi'   => (int)($di['sanitasi'] ?? 0),
                    'pasar'      => (int)($di['pasar'] ?? 0),
                    'spbu'       => (int)($di['spbu'] ?? 0),
                    'catatan_fasum' => $di['catatan_infrastruktur'] ?? null,
                ]);

                $assessment->dampakVital()->updateOrCreate(['id_assessment' => $id], [
                    'jalan'               => (float)($di['jalan_rusak_km'] ?? 0),
                    'air_bersih'          => isset($di['sarana_air_bersih_rusak']) ? (int)$di['sarana_air_bersih_rusak'] : 0,
                    'listrik'             => (int)($di['jaringan_listrik_padam_kk'] ?? 0),
                    'telekomunikasi'      => isset($di['jaringan_komunikasi_putus']) ? (int)$di['jaringan_komunikasi_putus'] : 0,
                    'sumber_air_tercemar' => isset($data['dampak_lingkungan']['sumber_air_tercemar']) ? (bool)$data['dampak_lingkungan']['sumber_air_tercemar'] : false,
                    'irigasi'             => (float)($di['irigasi'] ?? 0),
                    'sawah_ha'            => (float)($di['sawah_ha'] ?? 0),
                    'ternak_ekor'         => (int)($di['ternak_ekor'] ?? 0),
                    'hutan_ha'            => (float)($di['hutan_ha'] ?? 0),
                    'spbu'                => (int)($di['spbu'] ?? 0),
                    'catatan_vital'       => $di['catatan_infrastruktur'] ?? null,
                ]);
            }

            // ─── Dampak Lingkungan ─────────────────────────────────────────
            if (isset($data['dampak_lingkungan'])) {
                $dl = $data['dampak_lingkungan'];
                $mappedDl = [];
                if (isset($dl['lahan_pertanian_rusak_ha'])) $mappedDl['lahan_pertanian_rusak_ha'] = (float)$dl['lahan_pertanian_rusak_ha'];
                if (isset($dl['hutan_terdampak_ha'])) $mappedDl['hutan_terdampak_ha'] = (float)$dl['hutan_terdampak_ha'];
                if (isset($dl['lahan_tercemar_ha'])) $mappedDl['lahan_tercemar_ha'] = (float)$dl['lahan_tercemar_ha'];
                if (isset($dl['sumber_air_tercemar'])) $mappedDl['sumber_air_tercemar'] = (bool)$dl['sumber_air_tercemar'];
                if (isset($dl['pencemaran_tanah'])) $mappedDl['pencemaran_tanah'] = (bool)$dl['pencemaran_tanah'];
                if (isset($dl['erosi_sedimentasi'])) $mappedDl['erosi_sedimentasi'] = (bool)$dl['erosi_sedimentasi'];
                if (isset($dl['kerusakan_pesisir'])) $mappedDl['kerusakan_ekosistem_pesisir'] = (bool)$dl['kerusakan_pesisir'];
                if (isset($dl['kerusakan_ekosistem_pesisir'])) $mappedDl['kerusakan_ekosistem_pesisir'] = (bool)$dl['kerusakan_ekosistem_pesisir'];
                if (isset($dl['kerusakan_daerah_aliran_sungai'])) $mappedDl['kerusakan_daerah_aliran_sungai'] = (bool)$dl['kerusakan_daerah_aliran_sungai'];
                if (isset($dl['unggas'])) $mappedDl['ternak_unggas_ekor'] = (int)$dl['unggas'];
                if (isset($dl['kaki_empat'])) $mappedDl['ternak_kaki_empat_ekor'] = (int)$dl['kaki_empat'];
                if (isset($dl['perikanan_kolam'])) $mappedDl['perikanan_kolam_ha'] = (float)$dl['perikanan_kolam'];
                if (isset($dl['perikanan_kolam_ha'])) $mappedDl['perikanan_kolam_ha'] = (float)$dl['perikanan_kolam_ha'];
                if (isset($dl['perikanan_nelayan'])) $mappedDl['perikanan_nelayan_unit'] = (int)$dl['perikanan_nelayan'];
                if (isset($dl['perikanan_nelayan_unit'])) $mappedDl['perikanan_nelayan_unit'] = (int)$dl['perikanan_nelayan_unit'];
                if (isset($dl['catatan_lingkungan'])) $mappedDl['catatan_lingkungan'] = $dl['catatan_lingkungan'];

                $cleaned = $clean($mappedDl);
                if ($assessment->dampakLingkungan) {
                    $assessment->dampakLingkungan->update($cleaned);
                } else {
                    $assessment->dampakLingkungan()->create($cleaned);
                }
            }

            // ─── Dampak Ekonomi ────────────────────────────────────────────
            if (isset($data['dampak_ekonomi'])) {
                $de = $data['dampak_ekonomi'];
                $mappedDe = [];
                if (isset($de['persentase'])) $mappedDe['persentase_ekonomi_terdampak'] = $de['persentase'];
                if (isset($de['persentase_ekonomi_terdampak'])) $mappedDe['persentase_ekonomi_terdampak'] = $de['persentase_ekonomi_terdampak'];
                
                if (isset($de['sektor_1'])) $mappedDe['sektor_pencaharian_1'] = $de['sektor_1'];
                if (isset($de['sektor_pencaharian_1'])) $mappedDe['sektor_pencaharian_1'] = $de['sektor_pencaharian_1'];
                if (isset($de['kontribusi_1'])) $mappedDe['kontribusi_1'] = (float)$de['kontribusi_1'];
                if (isset($de['status_1'])) $mappedDe['status_terdampak_1'] = $de['status_1'];
                if (isset($de['status_terdampak_1'])) $mappedDe['status_terdampak_1'] = $de['status_terdampak_1'];
                
                if (isset($de['sektor_2'])) $mappedDe['sektor_pencaharian_2'] = $de['sektor_2'];
                if (isset($de['sektor_pencaharian_2'])) $mappedDe['sektor_pencaharian_2'] = $de['sektor_pencaharian_2'];
                if (isset($de['kontribusi_2'])) $mappedDe['kontribusi_2'] = (float)$de['kontribusi_2'];
                if (isset($de['status_2'])) $mappedDe['status_terdampak_2'] = $de['status_2'];
                if (isset($de['status_terdampak_2'])) $mappedDe['status_terdampak_2'] = $de['status_terdampak_2'];
                
                if (isset($de['sektor_3'])) $mappedDe['sektor_pencaharian_3'] = $de['sektor_3'];
                if (isset($de['sektor_pencaharian_3'])) $mappedDe['sektor_pencaharian_3'] = $de['sektor_pencaharian_3'];
                if (isset($de['kontribusi_3'])) $mappedDe['kontribusi_3'] = (float)$de['kontribusi_3'];
                if (isset($de['status_3'])) $mappedDe['status_terdampak_3'] = $de['status_3'];
                if (isset($de['status_terdampak_3'])) $mappedDe['status_terdampak_3'] = $de['status_terdampak_3'];
                
                if (isset($de['distribusi'])) $mappedDe['distribusi_hasil_panen'] = $de['distribusi'];
                if (isset($de['distribusi_hasil_panen'])) $mappedDe['distribusi_hasil_panen'] = $de['distribusi_hasil_panen'];
                if (isset($de['fasilitas'])) $mappedDe['fasilitas_pengolahan_kolektif'] = $de['fasilitas'];
                if (isset($de['fasilitas_pengolahan_kolektif'])) $mappedDe['fasilitas_pengolahan_kolektif'] = $de['fasilitas_pengolahan_kolektif'];
                if (isset($de['catatan_ekonomi'])) $mappedDe['catatan_ekonomi'] = $de['catatan_ekonomi'];

                $cleaned = $clean($mappedDe);
                if ($assessment->dampakEkonomi) {
                    $assessment->dampakEkonomi->update($cleaned);
                } else {
                    $assessment->dampakEkonomi()->create($cleaned);
                }
            }

            // ─── Biodata Kejadian (V1) & Lokasi Detail (V2) ────────────────
            if (isset($data['biodata_kejadian'])) {
                $cleaned = $clean($data['biodata_kejadian']);
                if ($assessment->biodataKejadian) {
                    $assessment->biodataKejadian->update($cleaned);
                } else {
                    $assessment->biodataKejadian()->create($cleaned);
                }
            }
            if (isset($data['cakupan_wilayah_deskripsi']) || isset($data['lokasi_detail'])) {
                $assessment->lokasiDetail()->updateOrCreate(['id_assessment' => $id], [
                    'alamat_spesifik'  => $data['cakupan_wilayah_deskripsi'] ?? null,
                    'id_kec'           => $data['lokasi_detail']['id_kec'] ?? null,
                    'id_desa'          => $data['lokasi_detail']['id_desa'] ?? null,
                    'region_terdampak' => $data['lokasi_detail']['region_terdampak'] ?? null,
                ]);
            }

            // ─── Narasi Kejadian (V1) & Narasi Detail (V2) ─────────────────
            if (isset($data['narasi_kejadian'])) {
                $assessment->narasiKejadian()->delete();
                $assessment->narasiKejadian()->create($clean($data['narasi_kejadian']));
            }
            if (isset($data['narasi_detail']) || isset($data['narasi_kejadian'])) {
                $assessment->narasiDetail()->updateOrCreate(['id_assessment' => $id], [
                    'kondisi_umum'     => $data['narasi_detail']['kondisi_umum'] ?? ($data['narasi_kejadian']['isi_narasi'] ?? null),
                    'sebaran_dampak'   => $data['narasi_detail']['sebaran_dampak'] ?? null,
                    'upaya_penanganan' => $data['narasi_detail']['upaya_penanganan'] ?? null,
                    'kendala_lapangan' => $data['narasi_detail']['kendala_lapangan'] ?? null,
                    'kendala_tambahan' => $data['narasi_detail']['kendala_tambahan'] ?? null,
                    'rekomendasi_aksi' => $data['narasi_detail']['rekomendasi_aksi'] ?? null,
                ]);
            }

            return $assessment->refresh()->load([
                'dampakManusiaV2', 'kebutuhanNumerik', 'lokasiDetail', 'narasiDetail',
                'dampakInfrastruktur', 'dampakRumah', 'dampakFasum', 'dampakVital',
                'dampakLingkungan', 'dampakEkonomi',
                'biodataKejadian', 'narasiKejadian',
            ]);
        });
    }

    /**
     * Simpan assessment LENGKAP dari form (semua section dalam satu transaksi).
     */
    public function simpanLengkap(array $data, OperasiInsiden $insiden, AuthUser $petugas): AssessmentUtama
    {
        return DB::transaction(function () use ($data, $insiden, $petugas) {
            $prev = $this->getPreviousAssessment($insiden->id_insiden);

            // ─── 1. TABEL UTAMA (assessment_utama) ───────────────────────
            $assessment = AssessmentUtama::create([
                'id_insiden'           => $insiden->id_insiden,
                'id_petugas_assessment' => $petugas->id_pengguna,
                'jenis_laporan'        => $data['jenis_laporan'] ?? 'kaji_cepat',
                'waktu_assesment'      => $data['event_date'] . ' ' . ($data['event_time'] ?? '12:00:00'),
                'cakupan_wilayah_deskripsi' => $data['alamat_spesifik'] ?? $data['region'] ?? 'Desa Terpilih',
                'latitude'             => $data['latitude'] ?? null,
                'longitude'            => $data['longitude'] ?? null,
                'is_latest'            => 1,
            ]);

            $id = $assessment->id_assessment_utama;

            // ─── 2. SECTION I — LOKASI DETAIL ────────────────────────────
            AssessmentLokasiDetail::create([
                'id_assessment'    => $id,
                'id_kec'           => $data['id_kecamatan'] ?? $data['kecamatan'] ?? null,
                'id_desa'          => $data['id_desa'] ?? $data['desa'] ?? null,
                'alamat_spesifik'  => $data['alamat_spesifik'] ?? null,
                'region_terdampak' => $data['region'] ?? null,
            ]);

            // ─── 3. SECTION II — NARASI ──────────────────────────────────
            AssessmentNarasiDetail::create([
                'id_assessment'    => $id,
                'sebaran_dampak'   => $data['sebaran_dampak'] ?? null,
                'kondisi_umum'     => $data['kondisi_mutakhir'] ?? null,
                'upaya_penanganan' => $data['upaya_penanganan'] ?? null,
                'kendala_lapangan' => $data['kendala_lapangan'] ?? null,
                'kendala_tambahan' => $data['kendala_tambahan'] ?? null,
                'rekomendasi_aksi' => $data['rekomendasi_aksi'] ?? null,
            ]);

            // ─── 4. SECTION III — KEBUTUHAN ESSAY ───────────────────────
            \Log::info('simpanLengkap data:', $data);
            $kebutuhan = $data['kebutuhan_lanjutan'] ?? [];

            AssessmentKebutuhanLanjutan::create([
                'id_assessment'       => $id,
                'kebutuhan_relawan'   => $kebutuhan['kebutuhan_relawan'] ?? null,
                'kebutuhan_logistik'  => $kebutuhan['kebutuhan_logistik'] ?? null,
                'kebutuhan_peralatan' => $kebutuhan['kebutuhan_peralatan'] ?? null,
                'kebutuhan_medis'     => $kebutuhan['kebutuhan_medis'] ?? null,
                'kebutuhan_pangan'    => $kebutuhan['kebutuhan_pangan'] ?? null,
                'kebutuhan_lainnya'   => $kebutuhan['kebutuhan_lainnya'] ?? null,
            ]);

            // ─── 5. SECTION III-b — KEBUTUHAN NUMERIK ────────────────────
            $needsNumeric = $data['needs_numeric'] ?? [];
            foreach ($needsNumeric as $kodeItem => $jumlah) {
                if ($jumlah <= 0) continue;

                $master = AssessmentKebutuhanNumerikMaster::where('kode_item', $kodeItem)->first();
                if (!$master) continue;

                AssessmentKebutuhanNumerik::create([
                    'id_assessment'    => $id,
                    'id_item'          => $master->id_item,
                    'jumlah_dibutuhkan' => (float) $jumlah,
                    'satuan'           => $master->satuan_default,
                ]);
            }

            // ─── 6. SECTION IV — DAMPAK MANUSIA ─────────────────────────
            $dampakManusia = $data['dampak_manusia'] ?? [];
            $prevDm = $prev?->dampakManusiaV2;
            $prevMeninggal = $prevDm ? $prevDm->meninggal : ($prev?->dampakManusia?->meninggal ?? 0);

            $meninggal = isset($dampakManusia['meninggal']) ? (int)$dampakManusia['meninggal'] : ($prevDm?->meninggal ?? 0);
            $meninggal = max($meninggal, $prevMeninggal);

            AssessmentDampakManusiaV2::create([
                'id_assessment'   => $id,
                'meninggal'       => $meninggal,
                'hilang'          => isset($dampakManusia['hilang']) ? (int)$dampakManusia['hilang'] : ($prevDm?->hilang ?? 0),
                'luka_berat'      => isset($dampakManusia['luka_berat']) ? (int)$dampakManusia['luka_berat'] : ($prevDm?->luka_berat ?? 0),
                'luka_ringan'     => isset($dampakManusia['luka_ringan']) ? (int)$dampakManusia['luka_ringan'] : ($prevDm?->luka_ringan ?? 0),
                'terdampak_jiwa'  => isset($dampakManusia['dampak_manusia']) ? (int)$dampakManusia['dampak_manusia'] : ($prevDm?->terdampak_jiwa ?? 0),
                'pengungsi_jiwa'  => isset($dampakManusia['pengungsi_jiwa']) ? (int)$dampakManusia['pengungsi_jiwa'] : ($prevDm?->pengungsi_jiwa ?? 0),
                'pengungsi_kk'    => isset($dampakManusia['pengungsi_kk']) ? (int)$dampakManusia['pengungsi_kk'] : ($prevDm?->pengungsi_kk ?? 0),
            ]);

            // ─── 7. SECTION V — KERUSAKAN RUMAH ─────────────────────────
            $dampakRumah = $data['dampak_rumah'] ?? [];
            $prevDr = $prev?->dampakRumah;
            AssessmentDampakRumah::create([
                'id_assessment' => $id,
                'rusak_berat'   => isset($dampakRumah['berat']) ? (int)$dampakRumah['berat'] : ($prevDr?->rusak_berat ?? 0),
                'rusak_sedang'  => isset($dampakRumah['sedang']) ? (int)$dampakRumah['sedang'] : ($prevDr?->rusak_sedang ?? 0),
                'rusak_ringan'  => isset($dampakRumah['ringan']) ? (int)$dampakRumah['ringan'] : ($prevDr?->rusak_ringan ?? 0),
            ]);

            // ─── 8. SECTION V-b — FASILITAS UMUM ────────────────────────
            $dampakFasum = $data['dampak_fasum'] ?? [];
            $prevDf = $prev?->dampakFasum;
            AssessmentDampakFasum::create([
                'id_assessment' => $id,
                'sanitasi'      => isset($dampakFasum['sanitas']) ? (int)$dampakFasum['sanitas'] : ($prevDf?->sanitasi ?? 0),
                'pendidikan'    => isset($dampakFasum['pendidikan']) ? (int)$dampakFasum['pendidikan'] : ($prevDf?->pendidikan ?? 0),
                'kesehatan'     => isset($dampakFasum['kesehatan']) ? (int)$dampakFasum['kesehatan'] : ($prevDf?->kesehatan ?? 0),
                'ibadah'        => isset($dampakFasum['ibadah']) ? (int)$dampakFasum['ibadah'] : ($prevDf?->ibadah ?? 0),
                'komunikasi'    => isset($dampakFasum['komunikasi']) ? (int)$dampakFasum['komunikasi'] : ($prevDf?->komunikasi ?? 0),
                'listrik'       => isset($dampakFasum['listrik']) ? (int)$dampakFasum['listrik'] : ($prevDf?->listrik ?? 0),
                'kantor'        => isset($dampakFasum['kantor']) ? (int)$dampakFasum['kantor'] : ($prevDf?->kantor ?? 0),
                'jembatan'      => isset($dampakFasum['jembatan']) ? (int)$dampakFasum['jembatan'] : ($prevDf?->jembatan ?? 0),
                'pasar'         => isset($dampakFasum['pasar']) ? (int)$dampakFasum['pasar'] : ($prevDf?->pasar ?? 0),
                'spbu'          => isset($dampakFasum['spbu']) ? (int)$dampakFasum['spbu'] : ($prevDf?->spbu ?? 0),
            ]);

            // ─── 9. SECTION VI — SARANA VITAL & LINGKUNGAN ───────────────
            $dampakVital = $data['dampak_vital'] ?? [];
            $prevDv = $prev?->dampakVital;
            AssessmentDampakVital::create([
                'id_assessment'       => $id,
                'air_bersih'          => isset($dampakVital['air']) ? (int)$dampakVital['air'] : ($prevDv?->air_bersih ?? 0),
                'listrik'             => isset($dampakVital['listrik']) ? (int)$dampakVital['listrik'] : ($prevDv?->listrik ?? 0),
                'telekomunikasi'      => isset($dampakVital['telkom']) ? (int)$dampakVital['telkom'] : ($prevDv?->telekomunikasi ?? 0),
                'irigasi'             => isset($dampakVital['irigasi']) ? (float)$dampakVital['irigasi'] : ($prevDv?->irigasi ?? 0),
                'jalan'               => isset($dampakVital['jalan']) ? (float)$dampakVital['jalan'] : ($prevDv?->jalan ?? 0),
                'spbu'                => isset($dampakVital['spbu']) ? (int)$dampakVital['spbu'] : ($prevDv?->spbu ?? 0),
            ]);

            $dampakLing = $data['dampak_lingkungan'] ?? [];
            $prevDl = $prev?->dampakLingkungan;
            $assessment->dampakLingkungan()->create([
                'lahan_pertanian_rusak_ha' => isset($dampakLing['sawah']) ? (float)$dampakLing['sawah'] : ($prevDl?->lahan_pertanian_rusak_ha ?? 0),
                'hutan_terdampak_ha'       => isset($dampakLing['hutan']) ? (float)$dampakLing['hutan'] : ($prevDl?->hutan_terdampak_ha ?? 0),
                'ternak_unggas_ekor'       => isset($dampakLing['unggas']) ? (int)$dampakLing['unggas'] : ($prevDl?->ternak_unggas_ekor ?? 0),
                'ternak_kaki_empat_ekor'   => isset($dampakLing['kaki_empat']) ? (int)$dampakLing['kaki_empat'] : ($prevDl?->ternak_kaki_empat_ekor ?? 0),
                'perikanan_kolam_ha'       => isset($dampakLing['perikanan_kolam']) ? (float)$dampakLing['perikanan_kolam'] : ($prevDl?->perikanan_kolam_ha ?? 0),
                'perikanan_nelayan_unit'   => isset($dampakLing['perikanan_nelayan']) ? (int)$dampakLing['perikanan_nelayan'] : ($prevDl?->perikanan_nelayan_unit ?? 0),
            ]);

            // ─── 10. SECTION VII — DAMPAK EKONOMI ─────────────────────────
            $de = $data['dampak_ekonomi'] ?? [];
            $prevDe = $prev?->dampakEkonomi;
            if (!empty($de) || $prevDe) {
                $assessment->dampakEkonomi()->create([
                    'persentase_ekonomi_terdampak' => isset($de['persentase']) ? $de['persentase'] : ($prevDe?->persentase_ekonomi_terdampak ?? null),
                    'sektor_pencaharian_1' => isset($de['sektor_1']) ? $de['sektor_1'] : ($prevDe?->sektor_pencaharian_1 ?? null),
                    'kontribusi_1' => isset($de['kontribusi_1']) ? (float)$de['kontribusi_1'] : ($prevDe?->kontribusi_1 ?? null),
                    'status_terdampak_1' => isset($de['status_1']) ? $de['status_1'] : ($prevDe?->status_terdampak_1 ?? null),
                    'sektor_pencaharian_2' => isset($de['sektor_2']) ? $de['sektor_2'] : ($prevDe?->sektor_pencaharian_2 ?? null),
                    'kontribusi_2' => isset($de['kontribusi_2']) ? (float)$de['kontribusi_2'] : ($prevDe?->kontribusi_2 ?? null),
                    'status_terdampak_2' => isset($de['status_2']) ? $de['status_2'] : ($prevDe?->status_terdampak_2 ?? null),
                    'sektor_pencaharian_3' => isset($de['sektor_3']) ? $de['sektor_3'] : ($prevDe?->sektor_pencaharian_3 ?? null),
                    'kontribusi_3' => isset($de['kontribusi_3']) ? (float)$de['kontribusi_3'] : ($prevDe?->kontribusi_3 ?? null),
                    'status_terdampak_3' => isset($de['status_3']) ? $de['status_3'] : ($prevDe?->status_terdampak_3 ?? null),
                    'distribusi_hasil_panen' => isset($de['distribusi']) ? $de['distribusi'] : ($prevDe?->distribusi_hasil_panen ?? null),
                    'fasilitas_pengolahan_kolektif' => isset($de['fasilitas']) ? $de['fasilitas'] : ($prevDe?->fasilitas_pengolahan_kolektif ?? null),
                ]);
            }

            // Automasi Transisi Status: Jika masih terverifikasi atau draft, naikkan ke respon
            $insidenFresh = $insiden->fresh();
            if (in_array($insidenFresh->status_insiden, ['draft', 'terverifikasi'])) {
                $this->insidenService->ubahStatus(
                    $insidenFresh,
                    'respon',
                    $petugas,
                    'Otomatis: Assessment berhasil disubmit oleh TRC di lapangan'
                );
            }

            return $assessment->fresh()->load($this->defaultRelations());
        });
    }

    /**
     * Update assessment LENGKAP (upsert semua extension tables).
     */
    public function updateLengkap(array $data, AssessmentUtama $assessment): AssessmentUtama
    {
        return DB::transaction(function () use ($data, $assessment) {
            // ─── 1. TABEL UTAMA ───────────────────────
            $assessment->update([
                'jenis_laporan'        => $data['jenis_laporan'] ?? $assessment->jenis_laporan,
                'waktu_assesment'      => isset($data['event_date']) ? ($data['event_date'] . ' ' . ($data['event_time'] ?? '12:00:00')) : $assessment->waktu_assesment,
                'latitude'             => $data['latitude'] ?? $assessment->latitude,
                'longitude'            => $data['longitude'] ?? $assessment->longitude,
            ]);

            $id = $assessment->id_assessment_utama;

            // ─── 2. SECTION I — LOKASI DETAIL ────────────────────────────
            $assessment->lokasiDetail()->updateOrCreate(['id_assessment' => $id], [
                'id_kec'           => $data['id_kecamatan'] ?? $data['kecamatan'] ?? null,
                'id_desa'          => $data['id_desa'] ?? $data['desa'] ?? null,
                'alamat_spesifik'  => $data['alamat_spesifik'] ?? null,
                'region_terdampak' => $data['region'] ?? null,
            ]);

            // ─── 3. SECTION II — NARASI ──────────────────────────────────
            $assessment->narasiDetail()->updateOrCreate(['id_assessment' => $id], [
                'sebaran_dampak'   => $data['sebaran_dampak'] ?? null,
                'kondisi_umum'     => $data['kondisi_mutakhir'] ?? null,
                'upaya_penanganan' => $data['upaya_penanganan'] ?? null,
                'kendala_lapangan' => $data['kendala_lapangan'] ?? null,
                'kendala_tambahan' => $data['kendala_tambahan'] ?? null,
                'rekomendasi_aksi' => $data['rekomendasi_aksi'] ?? null,
            ]);

            // ─── 4. SECTION III — KEBUTUHAN ESSAY ───────────────────────
            $kebutuhan = $data['kebutuhan'] ?? [];

            $assessment->kebutuhanLanjutan()->updateOrCreate(['id_assessment' => $id], [
                'kebutuhan_dana'      => $kebutuhan['dana'] ?? null,
                'kebutuhan_relawan'   => $kebutuhan['relawan'] ?? null,
                'kebutuhan_logistik'  => $kebutuhan['logistik'] ?? null,
                'kebutuhan_peralatan' => $kebutuhan['peralatan'] ?? null,
                'kebutuhan_medis'     => $kebutuhan['medis'] ?? null,
                'kebutuhan_pangan'    => $kebutuhan['pangan'] ?? null,
                'kebutuhan_lainnya'   => $kebutuhan['lainnya'] ?? null,
            ]);

            // ─── 5. SECTION III-b — KEBUTUHAN NUMERIK ────────────────────
            $needsNumeric = $data['needs_numeric'] ?? [];
            $assessment->kebutuhanNumerik()->delete();
            foreach ($needsNumeric as $kodeItem => $jumlah) {
                if ($jumlah <= 0) continue;

                $master = AssessmentKebutuhanNumerikMaster::where('kode_item', $kodeItem)->first();
                if (!$master) continue;

                $assessment->kebutuhanNumerik()->create([
                    'id_item'          => $master->id_item,
                    'jumlah_dibutuhkan' => (float) $jumlah,
                    'satuan'           => $master->satuan_default,
                ]);
            }

            // ─── 6. SECTION IV — DAMPAK MANUSIA ─────────────────────────
            $dampakManusia = $data['dampak_manusia'] ?? [];

            $assessment->dampakManusiaV2()->updateOrCreate(['id_assessment' => $id], [
                'meninggal'       => (int)($dampakManusia['meninggal'] ?? 0),
                'hilang'          => (int)($dampakManusia['hilang'] ?? 0),
                'luka_berat'      => (int)($dampakManusia['luka_berat'] ?? 0),
                'luka_ringan'     => (int)($dampakManusia['luka_ringan'] ?? 0),
                'terdampak_jiwa'  => (int)($dampakManusia['dampak_manusia'] ?? 0),
                'pengungsi_jiwa'  => (int)($dampakManusia['pengungsi_jiwa'] ?? 0),
                'pengungsi_kk'    => (int)($dampakManusia['pengungsi_kk'] ?? 0),
            ]);

            // ─── 7. SECTION V — KERUSAKAN RUMAH ─────────────────────────
            $dampakRumah = $data['dampak_rumah'] ?? [];
            $assessment->dampakRumah()->updateOrCreate(['id_assessment' => $id], [
                'rusak_berat'   => (int)($dampakRumah['berat'] ?? 0),
                'rusak_sedang'  => (int)($dampakRumah['sedang'] ?? 0),
                'rusak_ringan'  => (int)($dampakRumah['ringan'] ?? 0),
            ]);

            // ─── 8. SECTION V-b — FASILITAS UMUM ────────────────────────
            $dampakFasum = $data['dampak_fasum'] ?? [];
            $assessment->dampakFasum()->updateOrCreate(['id_assessment' => $id], [
                'sanitasi'      => (int)($dampakFasum['sanitas'] ?? 0),
                'pendidikan'    => (int)($dampakFasum['pendidikan'] ?? 0),
                'kesehatan'     => (int)($dampakFasum['kesehatan'] ?? 0),
                'ibadah'        => (int)($dampakFasum['ibadah'] ?? 0),
                'komunikasi'    => (int)($dampakFasum['komunikasi'] ?? 0),
                'listrik'       => (int)($dampakFasum['listrik'] ?? 0),
                'kantor'        => (int)($dampakFasum['kantor'] ?? 0),
                'jembatan'      => (int)($dampakFasum['jembatan'] ?? 0),
                'pasar'         => (int)($dampakFasum['pasar'] ?? 0),
                'spbu'          => (int)($dampakFasum['spbu'] ?? 0),
            ]);

            // ─── 9. SECTION VI — SARANA VITAL & LINGKUNGAN ───────────────
            $dampakVital = $data['dampak_vital'] ?? [];
            $assessment->dampakVital()->updateOrCreate(['id_assessment' => $id], [
                'air_bersih'          => (int)($dampakVital['air'] ?? 0),
                'listrik'             => (int)($dampakVital['listrik'] ?? 0),
                'telekomunikasi'      => (int)($dampakVital['telkom'] ?? 0),
                'irigasi'             => (float)($dampakVital['irigasi'] ?? 0),
                'jalan'               => (float)($dampakVital['jalan'] ?? 0),
                'spbu'                => (int)($dampakVital['spbu'] ?? 0),
            ]);

            $dampakLing = $data['dampak_lingkungan'] ?? [];
            $assessment->dampakLingkungan()->updateOrCreate(['id_assessment' => $id], [
                'lahan_pertanian_rusak_ha' => (float)($dampakLing['sawah'] ?? 0),
                'hutan_terdampak_ha'       => (float)($dampakLing['hutan'] ?? 0),
                'ternak_unggas_ekor'       => (int)($dampakLing['unggas'] ?? 0),
                'ternak_kaki_empat_ekor'   => (int)($dampakLing['kaki_empat'] ?? 0),
                'perikanan_kolam_ha'       => (float)($dampakLing['perikanan_kolam'] ?? 0),
                'perikanan_nelayan_unit'   => (int)($dampakLing['perikanan_nelayan'] ?? 0),
            ]);

            $de = $data['dampak_ekonomi'] ?? [];
            if (!empty($de)) {
                $assessment->dampakEkonomi()->updateOrCreate(['id_assessment' => $id], [
                    'persentase_ekonomi_terdampak' => $de['persentase'] ?? null,
                    'sektor_pencaharian_1' => $de['sektor_1'] ?? null,
                    'kontribusi_1' => isset($de['kontribusi_1']) ? (float)$de['kontribusi_1'] : null,
                    'status_terdampak_1' => $de['status_1'] ?? null,
                    'sektor_pencaharian_2' => $de['sektor_2'] ?? null,
                    'kontribusi_2' => isset($de['kontribusi_2']) ? (float)$de['kontribusi_2'] : null,
                    'status_terdampak_2' => $de['status_2'] ?? null,
                    'sektor_pencaharian_3' => $de['sektor_3'] ?? null,
                    'kontribusi_3' => isset($de['kontribusi_3']) ? (float)$de['kontribusi_3'] : null,
                    'status_terdampak_3' => $de['status_3'] ?? null,
                    'distribusi_hasil_panen' => $de['distribusi'] ?? null,
                    'fasilitas_pengolahan_kolektif' => $de['fasilitas'] ?? null,
                ]);
            }

            // Automasi Transisi Status untuk Update Assessment Lanjutan
            $insidenFresh = $assessment->insiden->fresh();
            if ($insidenFresh && in_array($insidenFresh->status_insiden, ['draft', 'terverifikasi'])) {
                // Untuk updateLengkap kita butuh petugas, karena fungsi updateLengkap tidak terima param petugas,
                // kita bisa cek Auth::user() jika tidak ada $petugas
                $aktor = auth()->user() ?: \App\Models\AuthUser::find($assessment->id_petugas_assessment);
                if ($aktor) {
                    $this->insidenService->ubahStatus(
                        $insidenFresh,
                        'respon',
                        $aktor,
                        'Otomatis: Assessment diperbarui oleh TRC di lapangan'
                    );
                }
            }

            return $assessment->fresh()->load($this->defaultRelations());
        });
    }

    /**
     * Relasi default untuk eager loading assessment lengkap.
     */
    public function defaultRelations(): array
    {
        return [
            'lokasiDetail.kecamatan', 'lokasiDetail.desa',
            'narasiDetail',
            'kebutuhanLanjutan',
            'kebutuhanNumerik.item',
            'dampakManusiaV2',
            'dampakRumah',
            'dampakFasum',
            'dampakVital',
            'dampakLingkungan',
            'dampakEkonomi',
            'petugas.profil',
        ];
    }

    /**
     * Format output kompatibel dengan struktur form lama.
     * Digunakan oleh API dan Blade untuk populate form edit.
     */
    public function keFormData(AssessmentUtama $assessment): array
    {
        $a = $assessment;
        return [
            // Section I
            'disaster_type'    => $a->insiden?->jenisBencana?->slug,
            'region'           => $a->lokasiDetail?->region_terdampak,
            'kecamatan'        => $a->lokasiDetail?->id_kec,
            'desa'             => $a->lokasiDetail?->id_desa,
            'alamat_spesifik'  => $a->lokasiDetail?->alamat_spesifik,
            'event_date'       => $a->waktu_assesment?->toDateString(),
            'event_time'       => $a->waktu_assesment?->format('H:i'),
            'latitude'         => $a->latitude,
            'longitude'        => $a->longitude,
            // Section II
            'kondisi_mutakhir' => $a->narasiDetail?->kondisi_umum,
            'upaya_penanganan' => $a->narasiDetail?->upaya_penanganan,
            'sebaran_dampak'   => $a->narasiDetail?->sebaran_dampak,
            // Section III
            'kebutuhan'        => [
                'dana'       => $a->kebutuhanLanjutan?->kebutuhan_dana,
                'relawan'    => $a->kebutuhanLanjutan?->kebutuhan_relawan,
                'logistik'   => $a->kebutuhanLanjutan?->kebutuhan_logistik,
                'peralatan'  => $a->kebutuhanLanjutan?->kebutuhan_peralatan,
                'medis'      => $a->kebutuhanLanjutan?->kebutuhan_medis ?? ($a->kebutuhanMendesak->first()?->kebutuhan_medis),
            ],
            // Section III-b
            'needs_numeric'    => $a->kebutuhanNumerik
                ?->pluck('jumlah_dibutuhkan', 'item.kode_item')
                ?->toArray() ?? [],
            // Section IV
            'dampak_manusia'   => [
                'meninggal'      => $a->dampakManusiaV2?->meninggal ?? $a->dampakManusia?->meninggal ?? 0,
                'hilang'         => $a->dampakManusiaV2?->hilang ?? $a->dampakManusia?->hilang ?? 0,
                'luka_berat'     => $a->dampakManusiaV2?->luka_berat ?? 0,
                'luka_ringan'    => $a->dampakManusiaV2?->luka_ringan ?? 0,
                'dampak_manusia' => $a->dampakManusiaV2?->terdampak_jiwa ?? $a->dampakManusia?->menderita_mengungsi ?? 0,
                'pengungsi_jiwa' => $a->dampakManusiaV2?->pengungsi_jiwa ?? 0,
                'pengungsi_kk'   => $a->dampakManusiaV2?->pengungsi_kk ?? 0,
            ],
            // Section V
            'dampak_rumah'     => [
                'berat'  => $a->dampakRumah?->rusak_berat  ?? 0,
                'sedang' => $a->dampakRumah?->rusak_sedang ?? 0,
                'ringan' => $a->dampakRumah?->rusak_ringan ?? 0,
            ],
            // Section V-b
            'dampak_fasum'     => [
                'sanitas'    => $a->dampakFasum?->sanitasi    ?? 0,
                'pendidikan' => $a->dampakFasum?->pendidikan  ?? 0,
                'kesehatan'  => $a->dampakFasum?->kesehatan   ?? 0,
                'ibadah'     => $a->dampakFasum?->ibadah      ?? 0,
                'komunikasi' => $a->dampakFasum?->komunikasi  ?? 0,
                'listrik'    => $a->dampakFasum?->listrik     ?? 0,
                'kantor'     => $a->dampakFasum?->kantor      ?? 0,
                'jembatan'   => $a->dampakFasum?->jembatan    ?? 0,
                'pasar'      => $a->dampakFasum?->pasar       ?? 0,
                'spbu'       => $a->dampakFasum?->spbu        ?? 0,
            ],
            // Section VI
            'dampak_vital'     => [
                'air'    => $a->dampakVital?->air_bersih     ?? 0,
                'listrik' => $a->dampakVital?->listrik       ?? 0,
                'telkom'  => $a->dampakVital?->telekomunikasi ?? 0,
                'irigasi' => $a->dampakVital?->irigasi        ?? 0,
                'jalan'   => $a->dampakVital?->jalan          ?? 0,
                'spbu'    => $a->dampakVital?->spbu           ?? 0,
            ],
            'dampak_lingkungan' => [
                'sawah'            => $a->dampakLingkungan?->lahan_pertanian_rusak_ha ?? 0,
                'hutan'            => $a->dampakLingkungan?->hutan_terdampak_ha      ?? 0,
                'unggas'           => $a->dampakLingkungan?->ternak_unggas_ekor      ?? 0,
                'kaki_empat'       => $a->dampakLingkungan?->ternak_kaki_empat_ekor  ?? 0,
                'perikanan_kolam'  => $a->dampakLingkungan?->perikanan_kolam_ha      ?? 0,
                'perikanan_nelayan'=> $a->dampakLingkungan?->perikanan_nelayan_unit  ?? 0,
            ],
            'dampak_ekonomi' => [
                'persentase'   => $a->dampakEkonomi?->persentase_ekonomi_terdampak,
                'sektor_1'     => $a->dampakEkonomi?->sektor_pencaharian_1,
                'kontribusi_1' => $a->dampakEkonomi?->kontribusi_1,
                'status_1'     => $a->dampakEkonomi?->status_terdampak_1,
                'sektor_2'     => $a->dampakEkonomi?->sektor_pencaharian_2,
                'kontribusi_2' => $a->dampakEkonomi?->kontribusi_2,
                'status_2'     => $a->dampakEkonomi?->status_terdampak_2,
                'sektor_3'     => $a->dampakEkonomi?->sektor_pencaharian_3,
                'kontribusi_3' => $a->dampakEkonomi?->kontribusi_3,
                'status_3'     => $a->dampakEkonomi?->status_terdampak_3,
                'distribusi'   => $a->dampakEkonomi?->distribusi_hasil_panen,
                'fasilitas'    => $a->dampakEkonomi?->fasilitas_pengolahan_kolektif,
            ],
        ];
    }
}
