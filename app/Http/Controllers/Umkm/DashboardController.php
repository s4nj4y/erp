<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Umkm\Concerns\ResolvesUmkm;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use ResolvesUmkm;

    public function index(Request $request): View
    {
        $umkm = $this->umkm($request);

        $stats = [
            'produk' => 0,
            'pesanan' => 0,
            'perlu_verifikasi' => 0,
            'pendapatan' => 0,
        ];

        if ($umkm) {
            $stats['produk'] = Produk::where('umkm_id', $umkm->id)->count();
            $stats['pesanan'] = Transaksi::where('umkm_id', $umkm->id)->count();
            $stats['perlu_verifikasi'] = Transaksi::where('umkm_id', $umkm->id)
                ->where('status_bayar', 'menunggu_verifikasi')->count();
            $stats['pendapatan'] = (int) TransaksiDetail::join('transaksi', 'transaksi.id', '=', 'transaksi_detail.transaksi_id')
                ->where('transaksi.umkm_id', $umkm->id)
                ->where('transaksi.status', 'selesai')
                ->sum(DB::raw('transaksi_detail.harga * transaksi_detail.qty'));
        }

        return view('umkm.dashboard', compact('umkm', 'stats'));
    }
}
