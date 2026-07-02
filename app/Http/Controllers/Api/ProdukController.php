<?php

namespace App\Http\Controllers\Api;

use App\Models\Produk;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProdukController extends ApiController
{
    #[OA\Get(path: '/api/produk', tags: ['Publik'], summary: 'Daftar produk tampil (show=true)',
        parameters: [
            new OA\Parameter(name: 'q', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'umkm', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'kategori', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Paginasi produk')])]
    public function index(Request $request)
    {
        $produk = Produk::with('umkm', 'kategori')
            ->where('show', true)
            ->when($request->umkm, fn ($q) => $q->where('umkm_id', $request->umkm))
            ->when($request->kategori, fn ($q) => $q->where('kategori_produk_id', $request->kategori))
            ->when($request->q, fn ($q) => $q->where('nama_produk', 'like', '%'.$request->q.'%'))
            ->latest()
            ->paginate(16);

        $produk->getCollection()->transform(fn ($p) => $p->append('gambar_url'));

        return $this->respond($produk);
    }

    #[OA\Get(path: '/api/produk/{produk}', tags: ['Publik'], summary: 'Detail produk + toko + atribut',
        parameters: [new OA\Parameter(name: 'produk', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Detail produk'),
            new OA\Response(response: 404, description: 'Tidak ada')])]
    public function show(Produk $produk)
    {
        abort_unless($produk->show, 404);

        return $this->respond($produk->load('umkm', 'kategori', 'detail.atribut')->append('gambar_url'));
    }
}
