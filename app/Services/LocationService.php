<?php

namespace App\Services;

use App\Models\WilayahKabupaten;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LocationService
{
    public function reverseGeocode(float $lat, float $lng): array
    {
        $cacheKey = 'geocode_' . (round($lat, 4)) . '_' . round($lng, 4);

        return Cache::remember($cacheKey, 3600, function () use ($lat, $lng) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'NuRiskApp/1.0 (contact@nurisk.test)'
                ])->timeout(5)->get('https://nominatim.openstreetmap.org/reverse', [
                    'lat' => $lat,
                    'lon' => $lng,
                    'format' => 'jsonv2',
                    'addressdetails' => 1,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $alamatLengkap = $data['display_name'] ?? 'Alamat tidak ditemukan';
                    $kabupaten = $this->extractKabupaten($data['address'] ?? [], $alamatLengkap);

                    if ($kabupaten) {
                        return [
                            'alamat_lengkap' => $alamatLengkap,
                            'kabupaten' => $kabupaten,
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Nominatim geocode failed', [
                    'lat' => $lat, 'lng' => $lng, 'error' => $e->getMessage(),
                ]);
            }

            return $this->fallbackGeocode($lat, $lng);
        });
    }

    public function findPcnuByKabupaten(?string $kabupaten): ?int
    {
        if (!$kabupaten) {
            return null;
        }

        $wilayah = WilayahKabupaten::where('nama_kab', 'LIKE', '%' . trim($kabupaten) . '%')->first();

        if ($wilayah) {
            $pcnu = DB::table('organisasi_pcnu')
                ->join('organisasi_unit', 'organisasi_pcnu.id_unit', '=', 'organisasi_unit.id_unit')
                ->where('organisasi_unit.id_wilayah', $wilayah->id_kab)
                ->select('organisasi_pcnu.id_pcnu')
                ->first();

            return $pcnu ? $pcnu->id_pcnu : null;
        }

        return null;
    }

    public function findPcnuByIdKab(?string $idKab): ?int
    {
        if (!$idKab) {
            return null;
        }

        $pcnu = DB::table('organisasi_pcnu')
            ->join('organisasi_unit', 'organisasi_pcnu.id_unit', '=', 'organisasi_unit.id_unit')
            ->where('organisasi_unit.id_wilayah', $idKab)
            ->select('organisasi_pcnu.id_pcnu')
            ->first();

        return $pcnu ? $pcnu->id_pcnu : null;
    }

    public function findPcnuByCoordinates(float $lat, float $lng): ?int
    {
        $geo = $this->reverseGeocode($lat, $lng);
        return $this->findPcnuByKabupaten($geo['kabupaten']);
    }

    public function getKabupatenByCoordinates(float $lat, float $lng): ?string
    {
        $geo = $this->reverseGeocode($lat, $lng);
        return $geo['kabupaten'];
    }

    private function extractKabupaten(array $address, string $displayName): ?string
    {
        $kabupaten = $address['city']
            ?? $address['county']
            ?? $address['municipality']
            ?? $address['state_district']
            ?? null;

        if ($kabupaten) {
            return str_replace(['Kabupaten ', 'Kota '], '', $kabupaten);
        }

        if (preg_match('/,\s*(Kabupaten|Kota)\s+([^,]+)/i', $displayName, $m)) {
            return trim($m[2]);
        }

        return null;
    }

    private function fallbackGeocode(float $lat, float $lng): array
    {
        $nearest = $this->findNearestKabupaten($lat, $lng);

        if ($nearest) {
            return [
                'alamat_lengkap' => $nearest['nama'] . ' (perkiraan dari koordinat)',
                'kabupaten' => str_replace(['Kabupaten ', 'Kota '], '', $nearest['nama']),
            ];
        }

        return [
            'alamat_lengkap' => "Lat: $lat, Lng: $lng",
            'kabupaten' => null,
        ];
    }

    private function findNearestKabupaten(float $lat, float $lng): ?array
    {
        $kabupatenList = config('kabupaten');
        if (empty($kabupatenList)) {
            return null;
        }

        $nearest = null;
        $minDistance = PHP_FLOAT_MAX;

        foreach ($kabupatenList as $kab) {
            if ($kab['lat'] === null || $kab['lng'] === null) {
                continue;
            }

            $distance = $this->haversineDistance($lat, $lng, $kab['lat'], $kab['lng']);
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearest = $kab;
            }
        }

        return $nearest;
    }

    private function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
