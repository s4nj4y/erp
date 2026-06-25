<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'umkm' => Umkm::count(),
            'produk' => Produk::count(),
            'customer' => User::where('role', 'customer')->count(),
            'transaksi' => Transaksi::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
