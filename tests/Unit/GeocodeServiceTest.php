<?php

namespace Tests\Unit;

use App\Services\GeocodeService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GeocodeServiceTest extends TestCase
{
    #[Test]
    public function format_address_prefers_road_and_area(): void
    {
        $service = new GeocodeService;

        $label = $service->formatAddress([
            'road' => 'Jalan Medan Merdeka Selatan',
            'suburb' => 'Gambir',
            'city' => 'Jakarta Pusat',
            'state' => 'Daerah Khusus Ibukota Jakarta',
        ]);

        $this->assertSame(
            'Jalan Medan Merdeka Selatan, Gambir, Daerah Khusus Ibukota Jakarta',
            $label
        );
    }

    #[Test]
    public function format_address_includes_place_name_when_present(): void
    {
        $service = new GeocodeService;

        $label = $service->formatAddress([
            'amenity' => 'Balai Kota DKI Jakarta',
            'road' => 'Jalan Medan Merdeka Selatan',
            'city' => 'Jakarta Pusat',
        ]);

        $this->assertSame(
            'Balai Kota DKI Jakarta, Jalan Medan Merdeka Selatan, Jakarta Pusat',
            $label
        );
    }

    #[Test]
    public function format_address_returns_null_when_empty(): void
    {
        $this->assertNull((new GeocodeService)->formatAddress([]));
    }

    #[Test]
    public function proxy_options_use_https_proxy_from_config(): void
    {
        config([
            'sipeni.http.http_proxy' => 'http://10.15.3.20:80',
            'sipeni.http.https_proxy' => 'http://10.15.3.20:80',
            'sipeni.http.no_proxy' => 'localhost,127.0.0.1,mysql',
        ]);

        $options = (new GeocodeService)->proxyOptions();

        $this->assertSame('http://10.15.3.20:80', $options['proxy']['https']);
        $this->assertContains('localhost', $options['proxy']['no']);
    }

    #[Test]
    public function proxy_options_skip_cidr_entries(): void
    {
        config([
            'sipeni.http.http_proxy' => 'http://10.15.3.20:80',
            'sipeni.http.https_proxy' => 'http://10.15.3.20:80',
            'sipeni.http.no_proxy' => 'localhost,127.0.0.1,10.0.0.0/8,mysql',
        ]);

        $options = (new GeocodeService)->proxyOptions();

        $this->assertContains('localhost', $options['proxy']['no']);
        $this->assertContains('mysql', $options['proxy']['no']);
        $this->assertNotContains('10.0.0.0/8', $options['proxy']['no']);
    }
}
