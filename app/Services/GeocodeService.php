<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeocodeService
{
    /** @var string|null */
    private ?string $lastError = null;

    public function lastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Reverse geocode lat/lng → nama jalan / tempat.
     * Mencoba beberapa provider (Nominatim → Photon → BigDataCloud).
     */
    public function reverse(float $latitude, float $longitude): ?string
    {
        $this->lastError = null;
        $providers = [
            'nominatim' => fn () => $this->fromNominatim($latitude, $longitude),
            'photon' => fn () => $this->fromPhoton($latitude, $longitude),
            'bigdatacloud' => fn () => $this->fromBigDataCloud($latitude, $longitude),
        ];

        $errors = [];
        foreach ($providers as $name => $resolver) {
            try {
                $label = $resolver();
                if (filled($label)) {
                    return mb_substr(trim((string) $label), 0, 500);
                }
                $errors[] = $name.': empty';
            } catch (Throwable $e) {
                $errors[] = $name.': '.$e->getMessage();
                Log::warning('Geocode provider gagal', [
                    'provider' => $name,
                    'lat' => $latitude,
                    'lng' => $longitude,
                    'error' => $e->getMessage(),
                    'proxy' => filled(config('sipeni.http.https_proxy') ?: config('sipeni.http.http_proxy')),
                ]);
            }
        }

        $this->lastError = implode(' | ', $errors);

        return null;
    }

    private function fromNominatim(float $latitude, float $longitude): ?string
    {
        $response = $this->httpClient()->get('https://nominatim.openstreetmap.org/reverse', [
            'lat' => $latitude,
            'lon' => $longitude,
            'format' => 'jsonv2',
            'addressdetails' => 1,
            'zoom' => 18,
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('HTTP '.$response->status().' '.mb_substr($response->body(), 0, 120));
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

        return $label;
    }

    private function fromPhoton(float $latitude, float $longitude): ?string
    {
        $response = $this->httpClient()->get('https://photon.komoot.io/reverse', [
            'lat' => $latitude,
            'lon' => $longitude,
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('HTTP '.$response->status().' '.mb_substr($response->body(), 0, 120));
        }

        $data = $response->json();
        $props = $data['features'][0]['properties'] ?? null;
        if (! is_array($props)) {
            return null;
        }

        $parts = array_values(array_filter([
            $props['name'] ?? null,
            $props['street'] ?? null,
            $props['district'] ?? null,
            $props['city'] ?? $props['town'] ?? $props['village'] ?? null,
            $props['state'] ?? null,
            $props['country'] ?? null,
        ], fn ($v) => filled($v)));

        return $parts === [] ? null : implode(', ', $parts);
    }

    private function fromBigDataCloud(float $latitude, float $longitude): ?string
    {
        $response = $this->httpClient()->get('https://api.bigdatacloud.net/data/reverse-geocode-client', [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'localityLanguage' => 'id',
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('HTTP '.$response->status().' '.mb_substr($response->body(), 0, 120));
        }

        $data = $response->json();
        if (! is_array($data)) {
            return null;
        }

        $parts = array_values(array_filter([
            $data['locality'] ?? null,
            $data['principalSubdivision'] ?? null,
            $data['countryName'] ?? null,
        ], fn ($v) => filled($v)));

        if ($parts !== []) {
            return implode(', ', $parts);
        }

        return isset($data['localityInfo']['informative'][0]['name'])
            ? (string) $data['localityInfo']['informative'][0]['name']
            : null;
    }

    /**
     * @return \Illuminate\Http\Client\PendingRequest
     */
    private function httpClient()
    {
        $request = Http::timeout(15)
            ->withHeaders([
                'User-Agent' => 'SI-MANTIK/1.0 ('.config('app.url').')',
                'Accept' => 'application/json',
                'Accept-Language' => 'id',
            ]);

        $options = $this->proxyOptions();
        if (! (bool) config('sipeni.http.geocode_ssl_verify', true)) {
            $options['verify'] = false;
        }
        if ($options !== []) {
            $request = $request->withOptions($options);
        }

        return $request;
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

        // Jangan masukkan CIDR 10.0.0.0/8 ke no_proxy Guzzle — bisa mengacaukan
        // routing ke proxy korporat di jaringan 10.x.
        $noProxy = array_values(array_filter(array_map(
            static fn (string $h): string => trim($h),
            explode(',', (string) config('sipeni.http.no_proxy', ''))
        ), static function (string $host): bool {
            if ($host === '') {
                return false;
            }
            // Skip CIDR besar yang mencakup host proxy (10.x)
            if (str_contains($host, '/')) {
                return false;
            }

            return true;
        }));

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
