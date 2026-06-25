<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriProduk;
use App\Models\KategoriProdukAtribut;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class KategoriProdukAtributController extends Controller
{
    public function store(Request $request, KategoriProduk $kategori_produk): RedirectResponse
    {
        $data = $request->validate(['atribut_produk' => 'required|string|max:100']);
        $kategori_produk->atribut()->create($data);

        return back()->with('success', 'Atribut ditambahkan.');
    }

    public function destroy(KategoriProdukAtribut $atribut): RedirectResponse
    {
        $atribut->delete();

        return back()->with('success', 'Atribut dihapus.');
    }
}
