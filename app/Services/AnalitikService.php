<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Analitik bisnis dari data transaksi. Semua metode agregat SQL murni
 * (portabel MySQL + SQLite), tanpa tabel tambahan.
 *
 * Penjualan = transaksi dengan status_bayar 'terverifikasi',
 * konsisten dengan laporan keuangan.
 */
class AnalitikService
{
    public const PERIODE = ['7d', '30d', '12m'];

    /** [Carbon $from, 'day'|'month' $granularitas, int $jumlahBucket] */
    private function range(string $periode): array
    {
        return match ($periode) {
            '7d' => [now()->subDays(6)->startOfDay(), 'day', 7],
            '12m' => [now()->startOfMonth()->subMonths(11), 'month', 12],
            default => [now()->subDays(29)->startOfDay(), 'day', 30],
        };
    }

    /** Base query penjualan terverifikasi (join detail), scope UMKM opsional. */
    private function penjualan(?int $umkmId, Carbon $from)
    {
        return DB::table('transaksi')
            ->join('transaksi_detail', 'transaksi_detail.transaksi_id', '=', 'transaksi.id')
            ->where('transaksi.status_bayar', 'terverifikasi')
            ->where('transaksi.tanggal', '>=', $from)
            ->when($umkmId, fn ($q) => $q->where('transaksi.umkm_id', $umkmId));
    }

    /**
     * Susun deret bucket berurutan tanpa lubang dari baris ber-kolom `tgl` (Y-m-d).
     * Baris harian diakumulasi ke bulan bila granularitas 'month'.
     */
    private function seri(Collection $rows, Carbon $from, string $gran, int $jumlah, array $kolom): array
    {
        $labels = [];
        $cursor = $from->copy();
        for ($i = 0; $i < $jumlah; $i++) {
            $labels[] = $gran === 'month' ? $cursor->format('Y-m') : $cursor->format('Y-m-d');
            $gran === 'month' ? $cursor->addMonth() : $cursor->addDay();
        }

        $seri = array_fill_keys($kolom, array_fill_keys($labels, 0));
        foreach ($rows as $row) {
            $key = $gran === 'month' ? substr($row->tgl, 0, 7) : $row->tgl;
            foreach ($kolom as $k) {
                if (isset($seri[$k][$key])) {
                    $seri[$k][$key] += (int) $row->$k;
                }
            }
        }

        return ['labels' => $labels] + array_map('array_values', $seri);
    }

    /** Tren omzet & jumlah transaksi per hari/bulan. $umkmId null = seluruh platform. */
    public function tren(?int $umkmId, string $periode): array
    {
        [$from, $gran, $jumlah] = $this->range($periode);

        $rows = $this->penjualan($umkmId, $from)
            ->selectRaw('DATE(transaksi.tanggal) as tgl,'
                .' SUM(transaksi_detail.harga * transaksi_detail.qty) as omzet,'
                .' COUNT(DISTINCT transaksi.id) as transaksi')
            ->groupBy('tgl')
            ->get();

        $data = $this->seri($rows, $from, $gran, $jumlah, ['omzet', 'transaksi']);
        $data['total_omzet'] = array_sum($data['omzet']);
        $data['total_transaksi'] = array_sum($data['transaksi']);

        return $data;
    }

    /** Top produk berdasarkan nilai penjualan pada periode. */
    public function produkTerlaris(int $umkmId, string $periode, int $limit = 10): Collection
    {
        [$from] = $this->range($periode);

        return $this->penjualan($umkmId, $from)
            ->join('produk', 'produk.id', '=', 'transaksi_detail.produk_id')
            ->selectRaw('produk.nama_produk as nama,'
                .' SUM(transaksi_detail.qty) as terjual,'
                .' SUM(transaksi_detail.harga * transaksi_detail.qty) as nilai')
            ->groupBy('transaksi_detail.produk_id', 'produk.nama_produk')
            ->orderByDesc('nilai')
            ->limit($limit)
            ->get();
    }

    /** Top pelanggan + jumlah pelanggan baru vs lama pada periode. */
    public function pelanggan(int $umkmId, string $periode, int $limit = 10): array
    {
        [$from] = $this->range($periode);

        $top = $this->penjualan($umkmId, $from)
            ->join('users', 'users.id', '=', 'transaksi.customer_id')
            ->selectRaw('users.name as nama,'
                .' COUNT(DISTINCT transaksi.id) as transaksi,'
                .' SUM(transaksi_detail.harga * transaksi_detail.qty) as belanja')
            ->groupBy('transaksi.customer_id', 'users.name')
            ->orderByDesc('belanja')
            ->limit($limit)
            ->get();

        // Pelanggan baru = transaksi terverifikasi pertamanya di toko ini jatuh pada periode.
        $pertama = DB::table('transaksi')
            ->where('umkm_id', $umkmId)
            ->where('status_bayar', 'terverifikasi')
            ->selectRaw('customer_id, MIN(tanggal) as pertama')
            ->groupBy('customer_id')
            ->pluck('pertama', 'customer_id');

        $aktif = DB::table('transaksi')
            ->where('umkm_id', $umkmId)
            ->where('status_bayar', 'terverifikasi')
            ->where('tanggal', '>=', $from)
            ->distinct()
            ->pluck('customer_id');

        $baru = $aktif->filter(fn ($id) => Carbon::parse($pertama[$id])->gte($from))->count();

        return [
            'top' => $top,
            'pelanggan_baru' => $baru,
            'pelanggan_lama' => $aktif->count() - $baru,
        ];
    }

    /** Pertumbuhan platform (admin): GMV, transaksi, UMKM baru, customer baru per bucket. */
    public function pertumbuhan(string $periode): array
    {
        [$from, $gran, $jumlah] = $this->range($periode);

        $gmv = $this->tren(null, $periode);

        $umkmBaru = DB::table('umkm')
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as tgl, COUNT(*) as jumlah')
            ->groupBy('tgl')->get();
        $customerBaru = DB::table('users')
            ->where('role', 'customer')
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as tgl, COUNT(*) as jumlah')
            ->groupBy('tgl')->get();

        $seriUmkm = $this->seri($umkmBaru, $from, $gran, $jumlah, ['jumlah']);
        $seriCustomer = $this->seri($customerBaru, $from, $gran, $jumlah, ['jumlah']);

        return [
            'labels' => $gmv['labels'],
            'gmv' => $gmv['omzet'],
            'transaksi' => $gmv['transaksi'],
            'umkm_baru' => $seriUmkm['jumlah'],
            'customer_baru' => $seriCustomer['jumlah'],
            'total_gmv' => $gmv['total_omzet'],
            'total_transaksi' => $gmv['total_transaksi'],
            'total_umkm_baru' => array_sum($seriUmkm['jumlah']),
            'total_customer_baru' => array_sum($seriCustomer['jumlah']),
        ];
    }

    /** Top UMKM berdasarkan GMV pada periode (admin). */
    public function umkmTeratas(string $periode, int $limit = 10): Collection
    {
        [$from] = $this->range($periode);

        return $this->penjualan(null, $from)
            ->join('umkm', 'umkm.id', '=', 'transaksi.umkm_id')
            ->selectRaw('umkm.nama_umkm as nama,'
                .' COUNT(DISTINCT transaksi.id) as transaksi,'
                .' SUM(transaksi_detail.harga * transaksi_detail.qty) as gmv')
            ->groupBy('transaksi.umkm_id', 'umkm.nama_umkm')
            ->orderByDesc('gmv')
            ->limit($limit)
            ->get();
    }
}
