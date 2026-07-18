<?php

namespace App\Console\Commands;

use App\Services\GeocodeService;
use Illuminate\Console\Command;

class GeocodeTestCommand extends Command
{
    protected $signature = 'sipeni:geocode-test
                            {lat=-6.175392 : Latitude}
                            {lng=106.827153 : Longitude}';

    protected $description = 'Uji reverse geocode (Nominatim/Photon/BigDataCloud) lewat proxy';

    public function handle(GeocodeService $geocode): int
    {
        $lat = (float) $this->argument('lat');
        $lng = (float) $this->argument('lng');

        $this->info('Proxy HTTP : '.((string) config('sipeni.http.http_proxy') ?: '(kosong)'));
        $this->info('Proxy HTTPS: '.((string) config('sipeni.http.https_proxy') ?: '(kosong)'));
        $this->info('SSL verify : '.(config('sipeni.http.geocode_ssl_verify', true) ? 'true' : 'false'));
        $this->info("Koordinat  : {$lat}, {$lng}");
        $this->newLine();

        $label = $geocode->reverse($lat, $lng);
        if ($label) {
            $this->info('OK: '.$label);

            return self::SUCCESS;
        }

        $this->error('GAGAL');
        if ($geocode->lastError()) {
            $this->line($geocode->lastError());
        }

        $this->newLine();
        $this->line('Tips: set GEOCODE_SSL_VERIFY=false di .env jika proxy MITM, lalu config:cache.');

        return self::FAILURE;
    }
}
