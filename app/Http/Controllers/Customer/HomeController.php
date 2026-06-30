<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\Umkm;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /** Daftar UMKM di halaman depan, dengan pencarian toko. */
    public function index(Request $request): View
    {
        $umkm = Umkm::with('jenisUsaha')
            ->withCount(['produk' => fn ($q) => $q->where('show', true)->where('stok', '>', 0)])
            ->where('status', true)
            ->when($request->q, fn ($query) => $query->where(fn ($w) => $w
                ->where('nama_umkm', 'like', '%'.$request->q.'%')
                ->orWhere('deskripsi', 'like', '%'.$request->q.'%')
                ->orWhereHas('jenisUsaha', fn ($j) => $j->where('nama_usaha', 'like', '%'.$request->q.'%'))
            ))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('home', compact('umkm'));
    }

    /** Halaman shop dengan filter kategori / toko. */
    public function shop(Request $request): View
    {
        $produk = Produk::with('umkm', 'kategori')
            ->where('show', true)
            ->when($request->umkm, fn ($q) => $q->where('umkm_id', $request->umkm))
            ->when($request->kategori, fn ($q) => $q->where('kategori_produk_id', $request->kategori))
            ->when($request->q, fn ($q) => $q->where('nama_produk', 'like', '%'.$request->q.'%'))
            ->latest()
            ->paginate(16)
            ->withQueryString();

        $kategori = KategoriProduk::orderBy('nama')->get();

        return view('shop', compact('produk', 'kategori'));
    }

    /** Detail toko UMKM beserta produknya. Hanya toko aktif yang tampil publik. */
    public function toko(Umkm $umkm): View
    {
        abort_unless($umkm->status, 404);

        $umkm->load('jenisUsaha');

        $produk = $umkm->produk()
            ->with('kategori')
            ->where('show', true)
            ->latest()
            ->paginate(12);

        return view('toko-detail', compact('umkm', 'produk'));
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
