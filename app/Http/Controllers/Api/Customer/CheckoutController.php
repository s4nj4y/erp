<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Api\ApiController;
use App\Models\KeranjangBelanja;
use App\Models\Stok;
use App\Models\Transaksi;
use App\Models\Umkm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class CheckoutController extends ApiController
{
    #[OA\Get(path: '/api/checkout/{umkm}', tags: ['Checkout'], summary: 'Ringkasan checkout per toko (item, total, rekening)', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'umkm', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Ringkasan'),
            new OA\Response(response: 422, description: 'Tidak ada item untuk toko ini')])]
    public function show(Request $request, Umkm $umkm)
    {
        $items = $this->cartItemsForUmkm($request, $umkm);

        if ($items->isEmpty()) {
            throw ValidationException::withMessages(['keranjang' => 'Tidak ada item untuk UMKM ini.']);
        }

        $umkm->load('rekening.bank');
        $items->each(fn ($i) => $i->produk?->append('gambar_url'));

        return $this->respond([
            'items' => $items,
            'total' => $items->sum(fn ($i) => $i->produk->harga * $i->qty),
            'rekening' => $umkm->rekening,
        ]);
    }

    #[OA\Post(path: '/api/checkout/{umkm}', tags: ['Checkout'], summary: 'Buat pesanan dari keranjang toko ini', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'umkm', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['bank_id'],
            properties: [new OA\Property(property: 'bank_id', type: 'integer')])),
        responses: [new OA\Response(response: 201, description: 'Transaksi dibuat'),
            new OA\Response(response: 422, description: 'Keranjang kosong / bank salah / stok kurang')])]
    public function store(Request $request, Umkm $umkm)
    {
        $items = $this->cartItemsForUmkm($request, $umkm);
        if ($items->isEmpty()) {
            throw ValidationException::withMessages(['keranjang' => 'Keranjang kosong.']);
        }

        $data = $request->validate([
            'bank_id' => ['required', Rule::exists('rekening_bank', 'bank_id')->where('umkm_id', $umkm->id)],
        ]);

        foreach ($items as $item) {
            if ($item->qty > $item->produk->stok) {
                throw ValidationException::withMessages(['stok' => "Stok '{$item->produk->nama_produk}' tidak mencukupi."]);
            }
        }

        $transaksi = DB::transaction(function () use ($request, $umkm, $items, $data) {
            $trx = Transaksi::create([
                'customer_id' => $request->user()->id,
                'umkm_id' => $umkm->id,
                'bank_id' => $data['bank_id'],
                'kode_transaksi' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
                'tanggal' => now(),
                'status' => 'pending',
                'status_bayar' => 'belum',
            ]);

            foreach ($items as $item) {
                $trx->detail()->create([
                    'produk_id' => $item->produk_id,
                    'qty' => $item->qty,
                    'harga' => $item->produk->harga,
                ]);

                $item->produk->decrement('stok', $item->qty);
                Stok::create([
                    'produk_id' => $item->produk_id,
                    'status' => 'keluar',
                    'jumlah_masuk' => 0,
                    'jumlah_keluar' => $item->qty,
                    'tanggal' => now()->toDateString(),
                    'keterangan' => 'Pesanan '.$trx->kode_transaksi,
                ]);

                $item->delete();
            }

            return $trx;
        });

        return $this->respond($transaksi, 'Pesanan dibuat. Silakan unggah bukti pembayaran.', 201);
    }

    private function cartItemsForUmkm(Request $request, Umkm $umkm)
    {
        return KeranjangBelanja::with('produk')
            ->where('user_id', $request->user()->id)
            ->whereHas('produk', fn ($q) => $q->where('umkm_id', $umkm->id))
            ->get();
    }
}
