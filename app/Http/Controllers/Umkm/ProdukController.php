<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Umkm\Concerns\ResolvesUmkm;
use App\Models\KategoriProduk;
use App\Models\Produk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProdukController extends Controller
{
    use ResolvesUmkm;

    public function index(Request $request): View|RedirectResponse
    {
        $umkm = $this->umkm($request);
        if (! $umkm) {
            return redirect()->route('umkm.profil.edit')->with('success', 'Lengkapi profil UMKM dulu.');
        }

        $produk = Produk::with('kategori')
            ->forUmkm($umkm)
            ->when($request->q, fn ($q) => $q->where('nama_produk', 'like', '%'.$request->q.'%'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('umkm.produk.index', compact('produk'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (! $this->umkm($request)) {
            return redirect()->route('umkm.profil.edit')->with('success', 'Lengkapi profil UMKM dulu.');
        }

        return view('umkm.produk.create', ['kategoriList' => KategoriProduk::orderBy('nama')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $umkm = $this->umkm($request);
        abort_if(! $umkm, 409);

        $data = $this->validateData($request);
        $data['umkm_id'] = $umkm->id;
        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('produk', 'public');
        }
        $data['show'] = $request->boolean('show');
        Produk::create($data);

        return redirect()->route('umkm.produk.index')->with('success', 'Produk ditambahkan.');
    }

    public function edit(Request $request, Produk $produk): View
    {
        $this->authorize('update', $produk);
        $produk->load('stoks');

        return view('umkm.produk.edit', [
            'produk' => $produk,
            'kategoriList' => KategoriProduk::orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, Produk $produk): RedirectResponse
    {
        $this->authorize('update', $produk);
        $data = $this->validateData($request);
        if ($request->hasFile('gambar')) {
            if ($produk->gambar) {
                Storage::disk('public')->delete($produk->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('produk', 'public');
        }
        $data['show'] = $request->boolean('show');
        $produk->update($data);

        return redirect()->route('umkm.produk.index')->with('success', 'Produk diperbarui.');
    }

    public function destroy(Request $request, Produk $produk): RedirectResponse
    {
        $this->authorize('delete', $produk);
        if ($produk->gambar) {
            Storage::disk('public')->delete($produk->gambar);
        }
        $produk->delete();

        return back()->with('success', 'Produk dihapus.');
    }

    public function toggleStatus(Request $request, Produk $produk): RedirectResponse
    {
        $this->authorize('update', $produk);
        $produk->update(['show' => ! $produk->show]);

        return back()->with('success', 'Status tampil diperbarui.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
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
