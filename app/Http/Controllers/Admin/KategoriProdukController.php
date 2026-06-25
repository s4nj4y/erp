<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriProduk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriProdukController extends Controller
{
    public function index(): View
    {
        $items = KategoriProduk::withCount('atribut', 'produk')->latest()->paginate(10);

        return view('admin.kategori-produk.index', compact('items'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['nama' => 'required|string|max:100']);
        KategoriProduk::create($data);

        return back()->with('success', 'Kategori ditambahkan.');
    }

    public function edit(KategoriProduk $kategori_produk): View
    {
        $kategori_produk->load('atribut');

        return view('admin.kategori-produk.edit', ['item' => $kategori_produk]);
    }

    public function update(Request $request, KategoriProduk $kategori_produk): RedirectResponse
    {
        $data = $request->validate(['nama' => 'required|string|max:100']);
        $kategori_produk->update($data);

        return redirect()->route('admin.kategori-produk.index')->with('success', 'Kategori diperbarui.');
    }

    public function destroy(KategoriProduk $kategori_produk): RedirectResponse
    {
        $kategori_produk->delete();

        return back()->with('success', 'Kategori dihapus.');
    }
}
