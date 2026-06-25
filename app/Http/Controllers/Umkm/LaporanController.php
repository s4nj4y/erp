<?php

namespace App\Http\Controllers\Umkm;

use App\Exports\LaporanExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Umkm\Concerns\ResolvesUmkm;
use App\Models\Saldo;
use App\Models\Transaksi;
use App\Models\TransaksiPengeluaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    use ResolvesUmkm;

    private function range(Request $request): array
    {
        $from = $request->date('from') ?: now()->startOfMonth();
        $to = $request->date('to') ?: now()->endOfDay();

        return [$from->startOfDay(), $to->endOfDay()];
    }

    private function rp(int|float $n): string
    {
        return 'Rp'.number_format($n, 0, ',', '.');
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

    public function labaRugi(Request $request)
    {
        $umkm = $this->umkm($request);
        if (! $umkm) {
            return redirect()->route('umkm.profil.edit')->with('success', 'Lengkapi profil UMKM dulu.');
        }
        [$from, $to] = $this->range($request);

        $penjualan = $this->penjualan($umkm->id, $from, $to);
        $pendapatan = $penjualan->sum(fn ($t) => $t->detail->sum(fn ($d) => $d->harga * $d->qty));
        $hpp = $penjualan->sum(fn ($t) => $t->detail->sum(fn ($d) => ($d->produk->harga_modal ?? 0) * $d->qty));
        $labaKotor = $pendapatan - $hpp;
        $pengeluaran = (int) TransaksiPengeluaran::where('umkm_id', $umkm->id)
            ->whereBetween('tanggal_pengeluaran', [$from, $to])->sum('total_harga');
        $labaBersih = $labaKotor - $pengeluaran;

        $rows = [
            ['Pendapatan Penjualan', $this->rp($pendapatan)],
            ['Harga Pokok Penjualan (HPP)', '('.$this->rp($hpp).')'],
            ['Laba Kotor', $this->rp($labaKotor)],
            ['Beban / Pengeluaran', '('.$this->rp($pengeluaran).')'],
        ];
        $summary = ['Laba Bersih' => $this->rp($labaBersih)];

        return $this->output($request, $umkm, 'Laporan Laba Rugi', [$from, $to],
            ['Keterangan', 'Jumlah'], $rows, $summary,
            'umkm.keuangan.laporan.laba-rugi',
            compact('pendapatan', 'hpp', 'labaKotor', 'pengeluaran', 'labaBersih', 'from', 'to'));
    }

    public function pendapatan(Request $request)
    {
        $umkm = $this->umkm($request);
        if (! $umkm) {
            return redirect()->route('umkm.profil.edit')->with('success', 'Lengkapi profil UMKM dulu.');
        }
        [$from, $to] = $this->range($request);

        $penjualan = $this->penjualan($umkm->id, $from, $to)->sortBy('tanggal');
        $rows = $penjualan->map(function (Transaksi $t) {
            $total = $t->detail->sum(fn ($d) => $d->harga * $d->qty);

            return [
                $t->tanggal->format('d/m/Y'),
                $t->kode_transaksi,
                $t->customer?->name ?? '-',
                $this->rp($total),
            ];
        })->values()->all();

        $totalPendapatan = $penjualan->sum(fn ($t) => $t->detail->sum(fn ($d) => $d->harga * $d->qty));
        $summary = ['Total Pendapatan' => $this->rp($totalPendapatan)];

        return $this->output($request, $umkm, 'Laporan Pendapatan', [$from, $to],
            ['Tanggal', 'Kode', 'Pembeli', 'Total'], $rows, $summary,
            'umkm.keuangan.laporan.pendapatan',
            compact('penjualan', 'totalPendapatan', 'from', 'to'));
    }

    public function perubahanModal(Request $request)
    {
        $umkm = $this->umkm($request);
        if (! $umkm) {
            return redirect()->route('umkm.profil.edit')->with('success', 'Lengkapi profil UMKM dulu.');
        }
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

        // laba bersih periode
        $penjualan = $this->penjualan($umkm->id, $from, $to);
        $pendapatan = $penjualan->sum(fn ($t) => $t->detail->sum(fn ($d) => $d->harga * $d->qty));
        $hpp = $penjualan->sum(fn ($t) => $t->detail->sum(fn ($d) => ($d->produk->harga_modal ?? 0) * $d->qty));
        $beban = (int) TransaksiPengeluaran::where('umkm_id', $umkm->id)
            ->whereBetween('tanggal_pengeluaran', [$from, $to])->sum('total_harga');
        $labaBersih = $pendapatan - $hpp - $beban;

        $modalAkhir = $modalAwal + $penambahan + $labaBersih - $pengambilan;

        $rows = [
            ['Modal Awal', $this->rp($modalAwal)],
            ['Penambahan Modal', $this->rp($penambahan)],
            ['Laba Bersih Periode', $this->rp($labaBersih)],
            ['Pengambilan Modal', '('.$this->rp($pengambilan).')'],
        ];
        $summary = ['Modal Akhir' => $this->rp($modalAkhir)];

        return $this->output($request, $umkm, 'Laporan Perubahan Modal', [$from, $to],
            ['Keterangan', 'Jumlah'], $rows, $summary,
            'umkm.keuangan.laporan.perubahan-modal',
            compact('modalAwal', 'penambahan', 'pengambilan', 'labaBersih', 'modalAkhir', 'from', 'to'));
    }

    /** Render HTML, atau export PDF/Excel sesuai ?export=. */
    private function output(Request $request, $umkm, string $title, array $rangeDates, array $headings, array $rows, array $summary, string $view, array $viewData)
    {
        [$from, $to] = $rangeDates;
        $periode = $from->format('d/m/Y').' – '.$to->format('d/m/Y');
        $export = $request->query('export');

        if ($export === 'pdf') {
            return Pdf::loadView('umkm.keuangan.laporan.pdf', compact('title', 'periode', 'headings', 'rows', 'summary', 'umkm'))
                ->download(str($title)->slug().'-'.now()->format('Ymd').'.pdf');
        }

        if ($export === 'excel') {
            return Excel::download(
                new LaporanExport($title, $headings, $rows, $summary),
                str($title)->slug().'-'.now()->format('Ymd').'.xlsx'
            );
        }

        return view($view, array_merge($viewData, [
            'umkm' => $umkm, 'title' => $title, 'periode' => $periode,
        ]));
    }
}
