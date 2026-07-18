<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeocodeService
{
    /**
     * Reverse geocode lat/lng → nama jalan / tempat (Nominatim OSM).
     */
    public function reverse(float $latitude, float $longitude): ?string
    {
        try {
            $request = Http::timeout(12)
                ->withHeaders([
                    'User-Agent' => 'SI-MANTIK/1.0 (bukti-sampai; '.config('app.url').')',
                    'Accept' => 'application/json',
                    'Accept-Language' => 'id',
                ]);

            $proxyOptions = $this->proxyOptions();
            if ($proxyOptions !== []) {
                $request = $request->withOptions($proxyOptions);
            }

            $response = $request->get('https://nominatim.openstreetmap.org/reverse', [
                'lat' => $latitude,
                'lon' => $longitude,
                'format' => 'jsonv2',
                'addressdetails' => 1,
                'zoom' => 18,
            ]);

            if (! $response->successful()) {
                Log::warning('Geocode reverse HTTP gagal', [
                    'lat' => $latitude,
                    'lng' => $longitude,
                    'status' => $response->status(),
                    'proxy' => filled(config('sipeni.http.https_proxy') ?: config('sipeni.http.http_proxy')),
                    'body' => mb_substr($response->body(), 0, 200),
                ]);

                return null;
            }

            $data = $response->json();
            if (! is_array($data)) {
                return null;
            }

            $label = $this->formatAddress(is_array($data['address'] ?? null) ? $data['address'] : []);
            if ($label === null || $label === '') {
                $label = isset($data['display_name']) && is_string($data['display_name'])
                    ? $data['display_name']
                    : null;
            }

            if ($label === null || $label === '') {
                return null;
            }

            return mb_substr(trim($label), 0, 500);
        } catch (Throwable $e) {
            Log::warning('Geocode reverse gagal', [
                'lat' => $latitude,
                'lng' => $longitude,
                'error' => $e->getMessage(),
                'proxy' => filled(config('sipeni.http.https_proxy') ?: config('sipeni.http.http_proxy')),
            ]);

            return null;
        }
    }

    /**
     * Opsi proxy Guzzle dari .env (HTTP_PROXY / HTTPS_PROXY).
     *
     * @return array<string, mixed>
     */
    public function proxyOptions(): array
    {
        $http = trim((string) config('sipeni.http.http_proxy', ''));
        $https = trim((string) config('sipeni.http.https_proxy', ''));
        $proxy = $https !== '' ? $https : $http;

        if ($proxy === '') {
            return [];
        }

        $noProxy = array_values(array_filter(array_map(
            static fn (string $h): string => trim($h),
            explode(',', (string) config('sipeni.http.no_proxy', ''))
        )));

        return [
            'proxy' => [
                'http' => $http !== '' ? $http : $proxy,
                'https' => $proxy,
                'no' => $noProxy,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $address
     */
    public function formatAddress(array $address): ?string
    {
        $road = $this->firstString($address, [
            'road', 'pedestrian', 'path', 'footway', 'residential', 'neighbourhood', 'suburb',
        ]);
        $place = $this->firstString($address, [
            'amenity', 'building', 'shop', 'office', 'tourism', 'leisure', 'public_building',
        ]);
        $area = $this->firstString($address, [
            'village', 'hamlet', 'suburb', 'city_district', 'municipality', 'city', 'town', 'county',
        ]);
        $state = $this->firstString($address, ['state', 'region']);

        $parts = array_values(array_filter([
            $place,
            $road,
            $area,
            $state,
        ], fn ($v) => filled($v)));

        // Hilangkan duplikat berurutan
        $unique = [];
        foreach ($parts as $part) {
            if ($unique === [] || end($unique) !== $part) {
                $unique[] = $part;
            }
        }

        return $unique === [] ? null : implode(', ', $unique);
    }

    /**
     * @param  array<string, mixed>  $address
     * @param  list<string>  $keys
     */
    private function firstString(array $address, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $address[$key] ?? null;
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }
}
