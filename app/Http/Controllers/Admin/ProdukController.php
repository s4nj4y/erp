<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\Umkm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProdukController extends Controller
{
    public function index(Request $request): View
    {
        $produk = Produk::with('umkm', 'kategori')
            ->when($request->umkm, fn ($q) => $q->where('umkm_id', $request->umkm))
            ->when($request->kategori, fn ($q) => $q->where('kategori_produk_id', $request->kategori))
            ->when($request->q, fn ($q) => $q->where('nama_produk', 'like', '%'.$request->q.'%'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.produk.index', [
            'produk' => $produk,
            'umkmList' => Umkm::orderBy('nama_umkm')->get(),
            'kategoriList' => KategoriProduk::orderBy('nama')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.produk.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('produk', 'public');
        }
        $data['show'] = $request->boolean('show');
        Produk::create($data);

        return redirect()->route('admin.produk.index')->with('success', 'Produk ditambahkan.');
    }

    public function edit(Produk $produk): View
    {
        $produk->load('stoks');

        return view('admin.produk.edit', array_merge($this->formData(), ['produk' => $produk]));
    }

    public function update(Request $request, Produk $produk): RedirectResponse
    {
        $data = $this->validateData($request);
        if ($request->hasFile('gambar')) {
            if ($produk->gambar) {
                Storage::disk('public')->delete($produk->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('produk', 'public');
        }
        $data['show'] = $request->boolean('show');
        $produk->update($data);

        return redirect()->route('admin.produk.index')->with('success', 'Produk diperbarui.');
    }

    public function destroy(Produk $produk): RedirectResponse
    {
        if ($produk->gambar) {
            Storage::disk('public')->delete($produk->gambar);
        }
        $produk->delete();

        return back()->with('success', 'Produk dihapus.');
    }

    public function toggleStatus(Produk $produk): RedirectResponse
    {
        $produk->update(['show' => ! $produk->show]);

        return back()->with('success', 'Status tampil produk diperbarui.');
    }

    private function formData(): array
    {
        return [
            'umkmList' => Umkm::orderBy('nama_umkm')->get(),
            'kategoriList' => KategoriProduk::orderBy('nama')->get(),
        ];
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'umkm_id' => ['required', Rule::exists('umkm', 'id')],
            'kategori_produk_id' => ['nullable', Rule::exists('kategori_produk', 'id')],
            'nama_produk' => 'required|string|max:100',
            'harga_modal' => 'required|integer|min:0',
            'harga' => 'required|integer|min:0',
            'stok' => 'required|integer|min:0',
            'deskripsi' => 'nullable|string',
            'berat' => 'nullable|string|max:100',
            'kandungan' => 'nullable|string|max:100',
            'warna' => 'nullable|string|max:100',
            'bahan' => 'nullable|string|max:100',
            'ukuran' => 'nullable|string|max:100',
            'gambar' => 'nullable|image|max:2048',
            'show' => 'boolean',
        ]);
    }
}
