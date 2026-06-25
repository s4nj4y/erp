<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $umkm = $request->user()->umkm;

        $stats = [
            'produk' => $umkm ? Produk::where('umkm_id', $umkm->id)->count() : 0,
            'transaksi' => $umkm ? Transaksi::where('umkm_id', $umkm->id)->count() : 0,
        ];

        return view('umkm.dashboard', compact('umkm', 'stats'));
    }
}
