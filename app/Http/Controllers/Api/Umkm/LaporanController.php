<?php

namespace App\Http\Controllers\Api\Umkm;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Umkm\Concerns\ResolvesUmkm;
use App\Models\Saldo;
use App\Models\Transaksi;
use App\Models\TransaksiPengeluaran;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class LaporanController extends ApiController
{
    use ResolvesUmkm;

    private function range(Request $request): array
    {
        $from = $request->date('from') ?: now()->startOfMonth();
        $to = $request->date('to') ?: now()->endOfDay();

        return [$from->startOfDay(), $to->endOfDay()];
    }

    /** Transaksi penjualan terverifikasi pada rentang. */
    private function penjualan(int $umkmId, $from, $to)
    {
        return Transaksi::with('detail.produk', 'customer')
            ->where('umkm_id', $umkmId)
            ->where('status_bayar', 'terverifikasi')
            ->whereBetween('tanggal', [$from, $to])
            ->get();
    }

    private function resolveUmkmOr409(Request $request)
    {
        $umkm = $this->umkm($request);
        abort_if(! $umkm, 409, 'Lengkapi profil UMKM dulu.');

        return $umkm;
    }

    #[OA\Get(path: '/api/umkm/laporan/laba-rugi', tags: ['UMKM Laporan'], summary: 'Laba rugi (?from=&to=, default bulan berjalan)', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'to', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date'))],
        responses: [new OA\Response(response: 200, description: 'pendapatan/hpp/laba_kotor/pengeluaran/laba_bersih')])]
    public function labaRugi(Request $request)
    {
        $umkm = $this->resolveUmkmOr409($request);
        [$from, $to] = $this->range($request);

        $penjualan = $this->penjualan($umkm->id, $from, $to);
        $pendapatan = $penjualan->sum(fn ($t) => $t->detail->sum(fn ($d) => $d->harga * $d->qty));
        $hpp = $penjualan->sum(fn ($t) => $t->detail->sum(fn ($d) => ($d->produk->harga_modal ?? 0) * $d->qty));
        $pengeluaran = (int) TransaksiPengeluaran::where('umkm_id', $umkm->id)
            ->whereBetween('tanggal_pengeluaran', [$from, $to])->sum('total_harga');

        return $this->respond([
            'pendapatan' => $pendapatan,
            'hpp' => $hpp,
            'laba_kotor' => $pendapatan - $hpp,
            'pengeluaran' => $pengeluaran,
            'laba_bersih' => $pendapatan - $hpp - $pengeluaran,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);
    }

    #[OA\Get(path: '/api/umkm/laporan/pendapatan', tags: ['UMKM Laporan'], summary: 'Rincian pendapatan per transaksi', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'to', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date'))],
        responses: [new OA\Response(response: 200, description: 'rows + total_pendapatan')])]
    public function pendapatan(Request $request)
    {
        $umkm = $this->resolveUmkmOr409($request);
        [$from, $to] = $this->range($request);

        $penjualan = $this->penjualan($umkm->id, $from, $to)->sortBy('tanggal');
        $rows = $penjualan->map(fn (Transaksi $t) => [
            'tanggal' => $t->tanggal->toDateString(),
            'kode' => $t->kode_transaksi,
            'pembeli' => $t->customer?->name ?? '-',
            'total' => $t->detail->sum(fn ($d) => $d->harga * $d->qty),
        ])->values()->all();

        return $this->respond([
            'rows' => $rows,
            'total_pendapatan' => $penjualan->sum(fn ($t) => $t->detail->sum(fn ($d) => $d->harga * $d->qty)),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);
    }

    #[OA\Get(path: '/api/umkm/laporan/perubahan-modal', tags: ['UMKM Laporan'], summary: 'Perubahan modal periode', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'to', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date'))],
        responses: [new OA\Response(response: 200, description: 'modal_awal/penambahan/laba_bersih/pengambilan/modal_akhir')])]
    public function perubahanModal(Request $request)
    {
        $umkm = $this->resolveUmkmOr409($request);
        [$from, $to] = $this->range($request);

        $modalAwal = (int) Saldo::where('umkm_id', $umkm->id)
            ->where('tanggal_transaksi', '<', $from)
            ->selectRaw("COALESCE(SUM(CASE WHEN jenis_transaksi='pengambilan_modal' THEN -jumlah ELSE jumlah END),0) as t")
            ->value('t');

        $penambahan = (int) Saldo::where('umkm_id', $umkm->id)
            ->whereBetween('tanggal_transaksi', [$from, $to])
            ->whereIn('jenis_transaksi', ['investasi_awal', 'penambahan_modal'])->sum('jumlah');
        $pengambilan = (int) Saldo::where('umkm_id', $umkm->id)
            ->whereBetween('tanggal_transaksi', [$from, $to])
            ->where('jenis_transaksi', 'pengambilan_modal')->sum('jumlah');

        $penjualan = $this->penjualan($umkm->id, $from, $to);
        $pendapatan = $penjualan->sum(fn ($t) => $t->detail->sum(fn ($d) => $d->harga * $d->qty));
        $hpp = $penjualan->sum(fn ($t) => $t->detail->sum(fn ($d) => ($d->produk->harga_modal ?? 0) * $d->qty));
        $beban = (int) TransaksiPengeluaran::where('umkm_id', $umkm->id)
            ->whereBetween('tanggal_pengeluaran', [$from, $to])->sum('total_harga');
        $labaBersih = $pendapatan - $hpp - $beban;

        return $this->respond([
            'modal_awal' => $modalAwal,
            'penambahan' => $penambahan,
            'laba_bersih' => $labaBersih,
            'pengambilan' => $pengambilan,
            'modal_akhir' => $modalAwal + $penambahan + $labaBersih - $pengambilan,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);
    }
}
