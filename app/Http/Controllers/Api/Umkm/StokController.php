<?php

namespace App\Http\Controllers\Api\Umkm;

use App\Http\Controllers\Api\ApiController;
use App\Models\Produk;
use App\Models\Stok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class StokController extends ApiController
{
    #[OA\Post(path: '/api/umkm/produk/{produk}/stok', tags: ['UMKM Produk'], summary: 'Catat pergerakan stok', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'produk', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['status', 'jumlah', 'tanggal'], properties: [
                new OA\Property(property: 'status', type: 'string', enum: ['masuk', 'keluar']),
                new OA\Property(property: 'jumlah', type: 'integer'),
                new OA\Property(property: 'tanggal', type: 'string', format: 'date'),
                new OA\Property(property: 'keterangan', type: 'string', nullable: true)])),
        responses: [new OA\Response(response: 201, description: 'Tercatat, stok produk ter-update'),
            new OA\Response(response: 422, description: 'Jumlah keluar melebihi stok')])]
    public function store(Request $request, Produk $produk)
    {
        $this->authorize('manageStock', $produk);

        $data = $request->validate([
            'status' => 'required|in:masuk,keluar',
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
        ]);

        $masuk = $data['status'] === 'masuk';
        if (! $masuk && $data['jumlah'] > $produk->stok) {
            throw ValidationException::withMessages(['jumlah' => 'Jumlah keluar melebihi stok.']);
        }

        $stok = DB::transaction(function () use ($produk, $data, $masuk) {
            $stok = Stok::create([
                'produk_id' => $produk->id,
                'status' => $data['status'],
                'jumlah_masuk' => $masuk ? $data['jumlah'] : 0,
                'jumlah_keluar' => $masuk ? 0 : $data['jumlah'],
                'tanggal' => $data['tanggal'],
                'keterangan' => $data['keterangan'] ?? null,
            ]);
            $produk->increment('stok', $masuk ? $data['jumlah'] : -$data['jumlah']);

            return $stok;
        });

        return $this->respond($stok, 'Pergerakan stok dicatat.', 201);
    }

    #[OA\Delete(path: '/api/umkm/stok/{stok}', tags: ['UMKM Produk'], summary: 'Hapus catatan stok (stok produk dikoreksi)', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'stok', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Terhapus')])]
    public function destroy(Request $request, Stok $stok)
    {
        $this->authorize('delete', $stok);

        DB::transaction(function () use ($stok) {
            $delta = $stok->jumlah_masuk - $stok->jumlah_keluar;
            $stok->produk?->decrement('stok', $delta);
            $stok->delete();
        });

        return $this->respond(message: 'Catatan stok dihapus.');
    }
}
