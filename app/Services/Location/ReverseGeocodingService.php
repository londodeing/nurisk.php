<?php

namespace App\Services\Location;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReverseGeocodingService
{
    const NOMINATIM_URL = 'https://nominatim.openstreetmap.org/reverse';

    public function getAddress(float $lat, float $lng): ?string
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'NURISK/1.0 (NU Peduli Jateng; nurisk@nupeduli.or.id)',
                'Accept-Language' => 'id',
            ])->timeout(5)->get(self::NOMINATIM_URL, [
                'format' => 'json',
                'lat'    => $lat,
                'lon'    => $lng,
                'zoom'   => 18,
            ]);

            if ($response->successful() && $data = $response->json()) {
                return $this->formatAddress($data);
            }
        } catch (\Exception $e) {
            Log::warning('[REVERSE_GEO] Gagal reverse geocoding', [
                'lat' => $lat, 'lng' => $lng, 'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    private function formatAddress(array $data): string
    {
        $addr = $data['address'] ?? [];
        $display = $data['display_name'] ?? '';

        $parts = [];
        if (!empty($addr['road'])) {
            $parts[] = $addr['road'];
        }
        if (!empty($addr['house_number'])) {
            $parts[] = 'No. ' . $addr['house_number'];
        }
        if (!empty($addr['village']) && $addr['village'] !== ($addr['city'] ?? '')) {
            $parts[] = $addr['village'];
        }
        if (!empty($addr['suburb'])) {
            $parts[] = $addr['suburb'];
        }
        if (!empty($addr['city_district'])) {
            $parts[] = $addr['city_district'];
        }
        if (!empty($addr['city'])) {
            $parts[] = $addr['city'];
        }
        if (!empty($addr['county'])) {
            $parts[] = $addr['county'];
        }

        $formatted = implode(', ', $parts);

        return $formatted ?: ($display ?: "{$lat}, {$lng}");
    }
}
