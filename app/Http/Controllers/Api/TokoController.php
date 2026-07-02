<?php

namespace App\Http\Controllers\Api;

use App\Models\Umkm;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TokoController extends ApiController
{
    #[OA\Get(path: '/api/toko', tags: ['Publik'], summary: 'Daftar toko UMKM aktif',
        parameters: [new OA\Parameter(name: 'q', in: 'query', required: false, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Paginasi toko')])]
    public function index(Request $request)
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
            ->paginate(12);

        return $this->respond($umkm);
    }

    #[OA\Get(path: '/api/toko/{umkm}', tags: ['Publik'], summary: 'Detail toko + info jenis usaha',
        parameters: [new OA\Parameter(name: 'umkm', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Detail toko'),
            new OA\Response(response: 404, description: 'Tidak ada / nonaktif')])]
    public function show(Umkm $umkm)
    {
        abort_unless($umkm->status, 404);

        return $this->respond($umkm->load('jenisUsaha'));
    }
}
