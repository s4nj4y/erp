<?php

namespace App\Http\Controllers\Api\Umkm;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Umkm\Concerns\ResolvesUmkm;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class ProdukController extends ApiController
{
    use ResolvesUmkm;

    #[OA\Get(path: '/api/umkm/produk', tags: ['UMKM Produk'], summary: 'Produk milik toko (cari ?q=)', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'q', in: 'query', required: false, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Paginasi produk'),
            new OA\Response(response: 409, description: 'Belum ada profil UMKM')])]
    public function index(Request $request)
    {
        $umkm = $this->umkm($request);
        abort_if(! $umkm, 409, 'Lengkapi profil UMKM dulu.');

        $produk = Produk::with('kategori')
            ->forUmkm($umkm)
            ->when($request->q, fn ($q) => $q->where('nama_produk', 'like', '%'.$request->q.'%'))
            ->latest()
            ->paginate(10);

        $produk->getCollection()->transform(fn ($p) => $p->append('gambar_url')->makeVisible('harga_modal'));

        return $this->respond($produk);
    }

    #[OA\Get(path: '/api/umkm/produk/{produk}', tags: ['UMKM Produk'], summary: 'Detail produk + riwayat stok', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'produk', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Produk + stoks'),
            new OA\Response(response: 404, description: 'Bukan milik toko user')])]
    public function show(Request $request, Produk $produk)
    {
        $this->authorize('update', $produk);

        return $this->respond($produk->load('kategori', 'stoks')->append('gambar_url')->makeVisible('harga_modal'));
    }

    #[OA\Post(path: '/api/umkm/produk', tags: ['UMKM Produk'], summary: 'Tambah produk (multipart utk gambar)', security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 201, description: 'Produk dibuat'),
            new OA\Response(response: 409, description: 'Belum ada profil UMKM')])]
    public function store(Request $request)
    {
        $umkm = $this->umkm($request);
        abort_if(! $umkm, 409, 'Lengkapi profil UMKM dulu.');

        $data = $this->validateData($request);
        $data['umkm_id'] = $umkm->id;
        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('produk', 'public');
        }
        $data['show'] = $request->boolean('show');
        $produk = Produk::create($data);

        return $this->respond($produk->append('gambar_url')->makeVisible('harga_modal'), 'Produk ditambahkan.', 201);
    }

    #[OA\Put(path: '/api/umkm/produk/{produk}', tags: ['UMKM Produk'], summary: 'Ubah produk (multipart pakai POST + _method=PUT)', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'produk', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Produk diperbarui')])]
    public function update(Request $request, Produk $produk)
    {
        $this->authorize('update', $produk);
        $data = $this->validateData($request);
        if ($request->hasFile('gambar')) {
            if ($produk->gambar && ! str_starts_with($produk->gambar, 'http')) {
                Storage::disk('public')->delete($produk->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('produk', 'public');
        }
        if ($request->has('show')) {
            $data['show'] = $request->boolean('show');
        }
        $produk->update($data);

        return $this->respond($produk->fresh()->append('gambar_url')->makeVisible('harga_modal'), 'Produk diperbarui.');
    }

    #[OA\Delete(path: '/api/umkm/produk/{produk}', tags: ['UMKM Produk'], summary: 'Hapus produk', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'produk', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Terhapus')])]
    public function destroy(Request $request, Produk $produk)
    {
        $this->authorize('delete', $produk);
        if ($produk->gambar && ! str_starts_with($produk->gambar, 'http')) {
            Storage::disk('public')->delete($produk->gambar);
        }
        $produk->delete();

        return $this->respond(message: 'Produk dihapus.');
    }

    #[OA\Patch(path: '/api/umkm/produk/{produk}/toggle', tags: ['UMKM Produk'], summary: 'Balik status tampil', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'produk', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Status tampil diperbarui')])]
    public function toggleStatus(Request $request, Produk $produk)
    {
        $this->authorize('update', $produk);
        $produk->update(['show' => ! $produk->show]);

        return $this->respond($produk->fresh()->append('gambar_url')->makeVisible('harga_modal'), 'Status tampil diperbarui.');
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
