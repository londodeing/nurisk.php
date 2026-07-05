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
    /**
     * Create a new Assessment atomically (Old flow).
     */
    public function createAssessment(array $data): AssessmentUtama
    {
        $insiden = OperasiInsiden::findOrFail($data['id_insiden']);

        // BR-ASSESSMENT-001
        if (!in_array($insiden->status_insiden, ['terverifikasi', 'respon'])) {
            throw new \InvalidArgumentException("Assessment hanya dapat dibuat untuk insiden yang sudah terverifikasi atau dalam tahap respon.");
        }

        return DB::transaction(function () use ($data) {
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
            if (isset($data['dampak_manusia'])) {
                $dm = $data['dampak_manusia'];

                AssessmentDampakManusiaV2::create([
                    'id_assessment'        => $id,
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

            // ─── Kebutuhan Mendesak (V1) ───────────────────────────────────
            if (isset($data['kebutuhan_mendesak']) && is_array($data['kebutuhan_mendesak'])) {
                foreach ($data['kebutuhan_mendesak'] as $kebutuhan) {
                    $utama->kebutuhanMendesak()->create($kebutuhan);
                }
            }

            // ─── Dampak Infrastruktur ─────────────────────────────────────
            if (isset($data['dampak_infrastruktur'])) {
                $di = $data['dampak_infrastruktur'];
                $utama->dampakInfrastruktur()->create($di);

                AssessmentDampakRumah::create([
                    'id_assessment' => $id,
                    'rusak_berat'   => (int)($di['rumah_rusak_berat'] ?? 0),
                    'rusak_sedang'  => (int)($di['rumah_rusak_sedang'] ?? 0),
                    'rusak_ringan'  => (int)($di['rumah_rusak_ringan'] ?? 0),
                    'terendam'      => (int)($di['rumah_terendam'] ?? 0),
                ]);

                AssessmentDampakFasum::create([
                    'id_assessment' => $id,
                    'pendidikan'    => (int)($di['fasilitas_pendidikan_rusak'] ?? 0),
                    'kesehatan'     => (int)($di['fasilitas_kesehatan_rusak'] ?? 0),
                    'ibadah'        => (int)($di['tempat_ibadah_rusak'] ?? 0),
                    'kantor'        => (int)($di['kantor_pemerintah_rusak'] ?? 0),
                    'jembatan'      => (int)($di['jembatan_putus'] ?? 0) + (int)($di['jembatan_rusak'] ?? 0),
                    'listrik'       => (int)($di['jaringan_listrik_padam_kk'] ?? 0),
                    'komunikasi'    => isset($di['jaringan_komunikasi_putus']) ? (int)$di['jaringan_komunikasi_putus'] : 0,
                    'catatan_fasum' => $di['catatan_infrastruktur'] ?? null,
                ]);

                AssessmentDampakVital::create([
                    'id_assessment'       => $id,
                    'jalan'               => (float)($di['jalan_rusak_km'] ?? 0),
                    'air_bersih'          => isset($di['sarana_air_bersih_rusak']) ? (int)$di['sarana_air_bersih_rusak'] : 0,
                    'listrik'             => (int)($di['jaringan_listrik_padam_kk'] ?? 0),
                    'telekomunikasi'      => isset($di['jaringan_komunikasi_putus']) ? (int)$di['jaringan_komunikasi_putus'] : 0,
                    'sumber_air_tercemar' => isset($data['dampak_lingkungan']['sumber_air_tercemar']) ? (bool)$data['dampak_lingkungan']['sumber_air_tercemar'] : false,
                    'catatan_vital'       => $di['catatan_infrastruktur'] ?? null,
                ]);
            }

            // ─── Dampak Lingkungan (V1) ────────────────────────────────────
            if (isset($data['dampak_lingkungan'])) {
                $utama->dampakLingkungan()->create($data['dampak_lingkungan']);
            }

            // ─── Dampak Ekonomi (V1) ───────────────────────────────────────
            if (isset($data['dampak_ekonomi'])) {
                $utama->dampakEkonomi()->create($data['dampak_ekonomi']);
            }

            // ─── Biodata Kejadian (V1) ──────────────────────────────────────
            if (isset($data['biodata_kejadian'])) {
                $utama->biodataKejadian()->create($data['biodata_kejadian']);
            }

            // ─── Narasi Kejadian (V1) ───────────────────────────────────────
            if (isset($data['narasi_kejadian'])) {
                $utama->narasiKejadian()->create($data['narasi_kejadian']);
            }

            return $utama->load([
                'dampakManusiaV2',
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

            // ─── Kebutuhan Mendesak ────────────────────────────────────────
            if (isset($data['kebutuhan_mendesak']) && is_array($data['kebutuhan_mendesak'])) {
                $assessment->kebutuhanMendesak()->delete();
                foreach ($data['kebutuhan_mendesak'] as $kebutuhan) {
                    $assessment->kebutuhanMendesak()->create($kebutuhan);
                }
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
                ]);

                $assessment->dampakFasum()->updateOrCreate(['id_assessment' => $id], [
                    'pendidikan' => (int)($di['fasilitas_pendidikan_rusak'] ?? 0),
                    'kesehatan'  => (int)($di['fasilitas_kesehatan_rusak'] ?? 0),
                    'ibadah'     => (int)($di['tempat_ibadah_rusak'] ?? 0),
                    'kantor'     => (int)($di['kantor_pemerintah_rusak'] ?? 0),
                    'jembatan'   => (int)($di['jembatan_putus'] ?? 0) + (int)($di['jembatan_rusak'] ?? 0),
                    'listrik'    => (int)($di['jaringan_listrik_padam_kk'] ?? 0),
                    'komunikasi' => isset($di['jaringan_komunikasi_putus']) ? (int)$di['jaringan_komunikasi_putus'] : 0,
                    'catatan_fasum' => $di['catatan_infrastruktur'] ?? null,
                ]);

                $assessment->dampakVital()->updateOrCreate(['id_assessment' => $id], [
                    'jalan'               => (float)($di['jalan_rusak_km'] ?? 0),
                    'air_bersih'          => isset($di['sarana_air_bersih_rusak']) ? (int)$di['sarana_air_bersih_rusak'] : 0,
                    'listrik'             => (int)($di['jaringan_listrik_padam_kk'] ?? 0),
                    'telekomunikasi'      => isset($di['jaringan_komunikasi_putus']) ? (int)$di['jaringan_komunikasi_putus'] : 0,
                    'sumber_air_tercemar' => isset($data['dampak_lingkungan']['sumber_air_tercemar']) ? (bool)$data['dampak_lingkungan']['sumber_air_tercemar'] : false,
                    'catatan_vital'       => $di['catatan_infrastruktur'] ?? null,
                ]);
            }

            // ─── Dampak Lingkungan ─────────────────────────────────────────
            if (isset($data['dampak_lingkungan'])) {
                if ($assessment->dampakLingkungan) {
                    $assessment->dampakLingkungan->update($data['dampak_lingkungan']);
                } else {
                    $assessment->dampakLingkungan()->create($data['dampak_lingkungan']);
                }
            }

            // ─── Dampak Ekonomi ────────────────────────────────────────────
            if (isset($data['dampak_ekonomi'])) {
                if ($assessment->dampakEkonomi) {
                    $assessment->dampakEkonomi->update($data['dampak_ekonomi']);
                } else {
                    $assessment->dampakEkonomi()->create($data['dampak_ekonomi']);
                }
            }

            // ─── Biodata Kejadian ──────────────────────────────────────────
            if (isset($data['biodata_kejadian'])) {
                if ($assessment->biodataKejadian) {
                    $assessment->biodataKejadian->update($data['biodata_kejadian']);
                } else {
                    $assessment->biodataKejadian()->create($data['biodata_kejadian']);
                }
            }

            // ─── Narasi Kejadian ───────────────────────────────────────────
            if (isset($data['narasi_kejadian'])) {
                $assessment->narasiKejadian()->delete();
                $assessment->narasiKejadian()->create($data['narasi_kejadian']);
            }

            return $assessment->refresh()->load([
                'dampakManusiaV2',
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
            $kebutuhan = $data['kebutuhan'] ?? [];

            AssessmentKebutuhanLanjutan::create([
                'id_assessment'       => $id,
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

            AssessmentDampakManusiaV2::create([
                'id_assessment'   => $id,
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
            AssessmentDampakRumah::create([
                'id_assessment' => $id,
                'rusak_berat'   => (int)($dampakRumah['berat'] ?? 0),
                'rusak_sedang'  => (int)($dampakRumah['sedang'] ?? 0),
                'rusak_ringan'  => (int)($dampakRumah['ringan'] ?? 0),
            ]);

            // ─── 8. SECTION V-b — FASILITAS UMUM ────────────────────────
            $dampakFasum = $data['dampak_fasum'] ?? [];
            AssessmentDampakFasum::create([
                'id_assessment' => $id,
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
            AssessmentDampakVital::create([
                'id_assessment'       => $id,
                'air_bersih'          => (int)($dampakVital['air'] ?? 0),
                'listrik'             => (int)($dampakVital['listrik'] ?? 0),
                'telekomunikasi'      => (int)($dampakVital['telkom'] ?? 0),
                'irigasi'             => (float)($dampakVital['irigasi'] ?? 0),
                'jalan'               => (float)($dampakVital['jalan'] ?? 0),
                'spbu'                => (int)($dampakVital['spbu'] ?? 0),
            ]);

            $dampakLing = $data['dampak_lingkungan'] ?? [];
            $assessment->dampakLingkungan()->create([
                'lahan_pertanian_rusak_ha' => (float)($dampakLing['sawah'] ?? 0),
                'ternak_terdampak_ekor'    => (int)($dampakLing['ternak'] ?? 0),
            ]);

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
                'ternak_terdampak_ekor'    => (int)($dampakLing['ternak'] ?? 0),
            ]);

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
                'sawah'  => $a->dampakLingkungan?->lahan_pertanian_rusak_ha ?? 0,
                'ternak' => $a->dampakLingkungan?->ternak_terdampak_ekor   ?? 0,
                'hutan'  => $a->dampakLingkungan?->hutan_terdampak_ha      ?? 0,
            ],
        ];
    }
}
