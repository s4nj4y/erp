<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Api\ApiController;
use App\Models\KeranjangBelanja;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class KeranjangController extends ApiController
{
    #[OA\Get(path: '/api/keranjang', tags: ['Keranjang'], summary: 'Isi keranjang user', security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Daftar item + produk + toko')])]
    public function index(Request $request)
    {
        $items = KeranjangBelanja::with('produk.umkm')
            ->where('user_id', $request->user()->id)
            ->get();

        $items->each(fn ($i) => $i->produk?->append('gambar_url'));

        return $this->respond($items);
    }

    #[OA\Post(path: '/api/keranjang/{produk}', tags: ['Keranjang'], summary: 'Tambah produk ke keranjang', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'produk', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [new OA\Property(property: 'qty', type: 'integer', default: 1)])),
        responses: [new OA\Response(response: 201, description: 'Item keranjang'),
            new OA\Response(response: 422, description: 'Produk tidak tersedia')])]
    public function store(Request $request, Produk $produk)
    {
        $qty = max(1, (int) $request->input('qty', 1));

        if (! $produk->show || $produk->stok < 1) {
            throw ValidationException::withMessages(['produk' => 'Produk tidak tersedia.']);
        }

        $item = KeranjangBelanja::firstOrNew([
            'user_id' => $request->user()->id,
            'produk_id' => $produk->id,
        ]);
        $item->qty = min($produk->stok, ($item->qty ?? 0) + $qty);
        $item->save();

        return $this->respond($item->load('produk.umkm'), 'Produk ditambahkan ke keranjang.', 201);
    }

    #[OA\Patch(path: '/api/keranjang/{keranjang}', tags: ['Keranjang'], summary: 'Ubah qty (action increase/decrease atau qty langsung)', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'keranjang', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: 'action', type: 'string', enum: ['increase', 'decrease']),
            new OA\Property(property: 'qty', type: 'integer')])),
        responses: [new OA\Response(response: 200, description: 'Item ter-update'),
            new OA\Response(response: 404, description: 'Bukan milik user')])]
    public function update(Request $request, KeranjangBelanja $keranjang)
    {
        $this->authorize('update', $keranjang);

        $action = $request->input('action');
        if ($action === 'increase') {
            $keranjang->qty = min($keranjang->produk->stok, $keranjang->qty + 1);
        } elseif ($action === 'decrease') {
            $keranjang->qty = max(1, $keranjang->qty - 1);
        } else {
            $keranjang->qty = max(1, min($keranjang->produk->stok, (int) $request->input('qty', $keranjang->qty)));
        }
        $keranjang->save();

        return $this->respond($keranjang->load('produk.umkm'));
    }

    #[OA\Delete(path: '/api/keranjang/{keranjang}', tags: ['Keranjang'], summary: 'Hapus item', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'keranjang', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Terhapus'),
            new OA\Response(response: 404, description: 'Bukan milik user')])]
    public function destroy(Request $request, KeranjangBelanja $keranjang)
    {
        $this->authorize('delete', $keranjang);
        $keranjang->delete();

        return $this->respond(message: 'Item dihapus dari keranjang.');
    }
}
