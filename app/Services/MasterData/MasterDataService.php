<?php

namespace App\Services\MasterData;

use App\Models\WilayahKabupaten;
use App\Models\WilayahKecamatan;
use App\Models\WilayahDesa;
use App\Models\OrganisasiPcnu;
use App\Models\OrganisasiMwc;
use App\Models\OrganisasiRanting;
use Illuminate\Support\Collection;

use Illuminate\Support\Facades\Cache;

class MasterDataService
{

    /**
     * Helper untuk mengambil data dari cache request-level.
     */
    private function resolve(string $key, callable $callback)
    {
        return Cache::remember("master_data_{$key}", now()->addHours(24), $callback);
    }

    /**
     * Mengosongkan cache (untuk keperluan testing).
     */
    public function clearCache(): void
    {
        // For testing, flush or we can clear specific keys if we tracked them
        Cache::flush();
    }

    // --- GEOGRAFIS WILAYAH ---

    public function getKabupatenList(): Collection
    {
        return $this->resolve('kabupaten_list', function () {
            return WilayahKabupaten::query()->orderBy('nama_kab')->get();
        });
    }

    public function getKecamatanByKabupaten(string $idKab): Collection
    {
        return $this->resolve("kecamatan_by_kab_{$idKab}", function () use ($idKab) {
            return WilayahKecamatan::query()
                ->where('id_kab', $idKab)
                ->orderBy('nama_kec')
                ->get();
        });
    }

    public function getDesaByKecamatan(string $idKec): Collection
    {
        return $this->resolve("desa_by_kec_{$idKec}", function () use ($idKec) {
            return WilayahDesa::query()
                ->where('id_kec', $idKec)
                ->orderBy('nama_desa')
                ->get();
        });
    }

    public function findKabupaten(string $idKab): ?WilayahKabupaten
    {
        return $this->resolve("kabupaten_find_{$idKab}", function () use ($idKab) {
            return WilayahKabupaten::query()->find($idKab);
        });
    }

    public function findKecamatan(string $idKec): ?WilayahKecamatan
    {
        return $this->resolve("kecamatan_find_{$idKec}", function () use ($idKec) {
            return WilayahKecamatan::query()->find($idKec);
        });
    }

    public function findDesa(string $idDesa): ?WilayahDesa
    {
        return $this->resolve("desa_find_{$idDesa}", function () use ($idDesa) {
            return WilayahDesa::query()->find($idDesa);
        });
    }

    // --- ELEMEN ORGANISASI NU ---

    public function getPcnuList(): Collection
    {
        return $this->resolve('pcnu_list', function () {
            return OrganisasiPcnu::query()->orderBy('nama_pcnu')->get();
        });
    }

    public function getMwcByPcnu(int $idPcnu): Collection
    {
        return $this->resolve("mwc_by_pcnu_{$idPcnu}", function () use ($idPcnu) {
            return OrganisasiMwc::query()
                ->where('id_pcnu', $idPcnu)
                ->orderBy('nama_mwc')
                ->get();
        });
    }

    public function getRantingByMwc(int $idMwc): Collection
    {
        return $this->resolve("ranting_by_mwc_{$idMwc}", function () use ($idMwc) {
            return OrganisasiRanting::query()
                ->where('id_mwc', $idMwc)
                ->orderBy('nama_ranting')
                ->get();
        });
    }

    public function findPcnu(int $idPcnu): ?OrganisasiPcnu
    {
        return $this->resolve("pcnu_find_{$idPcnu}", function () use ($idPcnu) {
            return OrganisasiPcnu::query()->find($idPcnu);
        });
    }

    public function findMwc(int $idMwc): ?OrganisasiMwc
    {
        return $this->resolve("mwc_find_{$idMwc}", function () use ($idMwc) {
            return OrganisasiMwc::query()->find($idMwc);
        });
    }

    public function findRanting(int $idRanting): ?OrganisasiRanting
    {
        return $this->resolve("ranting_find_{$idRanting}", function () use ($idRanting) {
            return OrganisasiRanting::query()->find($idRanting);
        });
    }
}
