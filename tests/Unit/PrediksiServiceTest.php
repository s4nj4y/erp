<?php

namespace Tests\Unit;

use App\Services\AnalitikService;
use App\Services\PrediksiService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/** Uji nilai batas ambang MIN_TITIK pada forecastSeri (boundary n=6 vs n=7). */
class PrediksiServiceTest extends TestCase
{
    private function forecast(array $nilai): ?array
    {
        $labels = [];
        for ($i = 1; $i <= count($nilai); $i++) {
            $labels[] = sprintf('2026-07-%02d', $i);
        }
        $svc = new PrediksiService(new AnalitikService());
        $m = new ReflectionMethod($svc, 'forecastSeri');

        return $m->invoke($svc, $nilai, $labels);
    }

    public function test_enam_titik_ditolak(): void
    {
        $this->assertNull($this->forecast(array_fill(0, 6, 10000)));
    }

    public function test_tujuh_titik_diterima_dengan_proyeksi_datar(): void
    {
        $f = $this->forecast(array_fill(0, 7, 10000));

        $this->assertNotNull($f);
        $this->assertSame('7 hari', $f['horizon']);
        $this->assertEqualsWithDelta(70000, $f['total'], 70);
    }

    public function test_tujuh_titik_semua_nol_ditolak(): void
    {
        $this->assertNull($this->forecast(array_fill(0, 7, 0)));
    }
}
