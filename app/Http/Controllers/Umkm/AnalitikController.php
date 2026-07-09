<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Umkm\Concerns\ResolvesUmkm;
use App\Services\AnalitikService;
use Illuminate\Http\Request;

class AnalitikController extends Controller
{
    use ResolvesUmkm;

    public function index(Request $request, AnalitikService $analitik)
    {
        $umkm = $this->umkm($request);
        if (! $umkm) {
            return redirect()->route('umkm.profil.edit')->with('success', 'Lengkapi profil UMKM dulu.');
        }

        $periode = $request->query('periode');
        if (! in_array($periode, AnalitikService::PERIODE, true)) {
            $periode = '30d';
        }

        $tren = $analitik->tren($umkm->id, $periode);
        $produk = $analitik->produkTerlaris($umkm->id, $periode);
        $pelanggan = $analitik->pelanggan($umkm->id, $periode);
        $aov = $tren['total_transaksi'] > 0
            ? intdiv($tren['total_omzet'], $tren['total_transaksi']) : 0;

        return view('umkm.analitik', compact('umkm', 'periode', 'tren', 'produk', 'pelanggan', 'aov'));
    }
}
