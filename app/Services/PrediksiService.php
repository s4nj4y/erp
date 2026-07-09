<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Phpml\Regression\LeastSquares;

/**
 * Prediksi bisnis di atas deret historis AnalitikService, regresi linear
 * php-ai/php-ml (LeastSquares) dilatih on-the-fly — data masih kecil,
 * training hitungannya milidetik sehingga tak perlu model tersimpan.
 */
class PrediksiService
{
    /** Minimal titik data historis agar regresi layak dipakai. */
    private const MIN_TITIK = 7;

    public function __construct(private AnalitikService $analitik)
    {
    }

    /** Proyeksi omzet ($umkmId) / GMV platform (null). Null bila data belum cukup. */
    public function forecastOmzet(?int $umkmId, string $periode): ?array
    {
        $tren = $this->analitik->tren($umkmId, $periode);

        return $this->forecastSeri($tren['omzet'], $tren['labels']);
    }

    /** Proyeksi pendaftaran & GMV platform (admin). */
    public function forecastPertumbuhan(string $periode): array
    {
        $p = $this->analitik->pertumbuhan($periode);

        return [
            'gmv' => $this->forecastSeri($p['gmv'], $p['labels']),
            'umkm_baru' => $this->forecastSeri($p['umkm_baru'], $p['labels']),
            'customer_baru' => $this->forecastSeri($p['customer_baru'], $p['labels']),
        ];
    }

    /**
     * Estimasi kapan stok produk habis dari laju jual harian 30 hari terakhir.
     * ponytail: aritmetika laju sederhana, bukan ML — regresi tak menambah akurasi di sini.
     */
    public function stokHabis(int $umkmId, int $limit = 10): Collection
    {
        return $this->penjualanProduk($umkmId)
            ->selectRaw('produk.nama_produk as nama, produk.stok as stok,'
                .' SUM(transaksi_detail.qty) as terjual')
            ->where('produk.stok', '>', 0)
            ->groupBy('transaksi_detail.produk_id', 'produk.nama_produk', 'produk.stok')
            ->get()
            ->map(function ($row) {
                $laju = round($row->terjual / 30, 2); // qty per hari
                $row->laju = $laju;
                $row->hari_tersisa = (int) ceil($row->stok / $laju);

                return $row;
            })
            ->sortBy('hari_tersisa')
            ->take($limit)
            ->values();
    }

    /** Produk dengan momentum penjualan naik: slope regresi qty harian 30 hari. */
    public function produkTrending(int $umkmId, int $limit = 5): Collection
    {
        $rows = $this->penjualanProduk($umkmId)
            ->selectRaw('transaksi_detail.produk_id, produk.nama_produk as nama,'
                .' DATE(transaksi.tanggal) as tgl, SUM(transaksi_detail.qty) as qty')
            ->groupBy('transaksi_detail.produk_id', 'produk.nama_produk', 'tgl')
            ->get()
            ->groupBy('produk_id');

        return $rows->map(function (Collection $harian) {
            // deret 30 hari penuh (hari tanpa penjualan = 0) agar slope adil antar produk
            $mulai = now()->subDays(29)->startOfDay();
            $seri = array_fill(0, 30, 0);
            foreach ($harian as $row) {
                $i = (int) $mulai->diffInDays(Carbon::parse($row->tgl), false);
                if ($i >= 0 && $i < 30) {
                    $seri[$i] += (int) $row->qty;
                }
            }

            return (object) [
                'nama' => $harian->first()->nama,
                'terjual' => array_sum($seri),
                'slope' => round($this->slope($seri), 3),
            ];
        })
            ->filter(fn ($p) => $p->slope > 0)
            ->sortByDesc('slope')
            ->take($limit)
            ->values();
    }

    /** Base query penjualan terverifikasi 30 hari terakhir, join detail + produk. */
    private function penjualanProduk(int $umkmId)
    {
        return DB::table('transaksi')
            ->join('transaksi_detail', 'transaksi_detail.transaksi_id', '=', 'transaksi.id')
            ->join('produk', 'produk.id', '=', 'transaksi_detail.produk_id')
            ->where('transaksi.umkm_id', $umkmId)
            ->where('transaksi.status_bayar', 'terverifikasi')
            ->where('transaksi.tanggal', '>=', now()->subDays(29)->startOfDay());
    }

    /**
     * Regresi deret -> proyeksi horizon berikutnya (7 hari utk deret harian,
     * 3 bulan utk bulanan). Null bila titik < MIN_TITIK atau semuanya nol.
     */
    private function forecastSeri(array $nilai, array $labels): ?array
    {
        $n = count($nilai);
        if ($n < self::MIN_TITIK || array_sum($nilai) <= 0) {
            return null;
        }

        $bulanan = strlen($labels[0]) === 7; // 'Y-m' vs 'Y-m-d'
        $horizon = $bulanan ? 3 : 7;

        $reg = new LeastSquares();
        $reg->train(
            array_map(fn ($i) => [$i], range(0, $n - 1)),
            array_map('floatval', $nilai)
        );
        $prediksi = $reg->predict(array_map(fn ($i) => [$i], range($n, $n + $horizon - 1)));

        $cursor = $bulanan
            ? Carbon::parse(end($labels).'-01')
            : Carbon::parse(end($labels));
        $labelBaru = [];
        for ($i = 0; $i < $horizon; $i++) {
            $bulanan ? $cursor->addMonth() : $cursor->addDay();
            $labelBaru[] = $bulanan ? $cursor->format('Y-m') : $cursor->format('Y-m-d');
        }

        $nilaiBaru = array_map(fn ($v) => max(0, (int) round($v)), $prediksi);

        return [
            'labels' => $labelBaru,
            'nilai' => $nilaiBaru,
            'total' => array_sum($nilaiBaru),
            'horizon' => $bulanan ? '3 bulan' : '7 hari',
        ];
    }

    /** Kemiringan tren deret (unit per hari) via LeastSquares dua titik ujung. */
    private function slope(array $seri): float
    {
        if (array_sum($seri) <= 0) {
            return 0.0;
        }

        $reg = new LeastSquares();
        $reg->train(
            array_map(fn ($i) => [$i], range(0, count($seri) - 1)),
            array_map('floatval', $seri)
        );

        return $reg->predict([count($seri)]) - $reg->predict([count($seri) - 1]);
    }
}
