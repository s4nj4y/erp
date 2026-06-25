<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\KategoriProduk;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /** Katalog ringkas di halaman depan. */
    public function index(): View
    {
        $produk = Produk::with('umkm', 'kategori')
            ->where('show', true)
            ->where('stok', '>', 0)
            ->latest()
            ->paginate(16);

        return view('home', compact('produk'));
    }

    /** Halaman shop dengan filter kategori. */
    public function shop(Request $request): View
    {
        $produk = Produk::with('umkm', 'kategori')
            ->where('show', true)
            ->when($request->kategori, fn ($q) => $q->where('kategori_produk_id', $request->kategori))
            ->when($request->q, fn ($q) => $q->where('nama_produk', 'like', '%'.$request->q.'%'))
            ->latest()
            ->paginate(16)
            ->withQueryString();

        $kategori = KategoriProduk::orderBy('nama')->get();

        return view('shop', compact('produk', 'kategori'));
    }

    /** Detail produk — guard 404 bila tidak ada (pelajaran dari bug app lama). */
    public function show(Produk $produk): View
    {
        $produk->load('umkm', 'kategori', 'detail.atribut');

        $produkLain = Produk::where('umkm_id', $produk->umkm_id)
            ->where('id', '!=', $produk->id)
            ->where('show', true)
            ->take(4)
            ->get();

        return view('produk-detail', compact('produk', 'produkLain'));
    }
}
