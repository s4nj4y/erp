<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Api\ApiController;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class TransaksiController extends ApiController
{
    #[OA\Get(path: '/api/transaksi', tags: ['Transaksi Customer'], summary: 'Riwayat pesanan user', security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Paginasi transaksi')])]
    public function index(Request $request)
    {
        $transaksi = Transaksi::with('umkm')
            ->withCount('detail')
            ->where('customer_id', $request->user()->id)
            ->latest('tanggal')
            ->paginate(10);

        return $this->respond($transaksi);
    }

    #[OA\Get(path: '/api/transaksi/{transaksi}', tags: ['Transaksi Customer'], summary: 'Detail pesanan + total', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'transaksi', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Detail + total'),
            new OA\Response(response: 404, description: 'Bukan milik user')])]
    public function show(Request $request, Transaksi $transaksi)
    {
        $this->authorize('viewAsCustomer', $transaksi);
        $transaksi->load('umkm.rekening.bank', 'bank', 'detail.produk');
        $transaksi->detail->each(fn ($d) => $d->produk?->append('gambar_url'));
        $transaksi->append('bukti_pembayaran_url');

        return $this->respond([
            'transaksi' => $transaksi,
            'total' => $transaksi->detail->sum(fn ($d) => $d->harga * $d->qty),
        ]);
    }

    #[OA\Post(path: '/api/transaksi/{transaksi}/bukti', tags: ['Transaksi Customer'], summary: 'Upload bukti pembayaran (multipart)', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'transaksi', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(mediaType: 'multipart/form-data',
            schema: new OA\Schema(required: ['bukti_pembayaran'], properties: [
                new OA\Property(property: 'bukti_pembayaran', type: 'string', format: 'binary')]))),
        responses: [new OA\Response(response: 200, description: 'Bukti tersimpan, status_bayar menunggu_verifikasi'),
            new OA\Response(response: 422, description: 'Bukan gambar / terlalu besar')])]
    public function uploadBukti(Request $request, Transaksi $transaksi)
    {
        $this->authorize('viewAsCustomer', $transaksi);
        if (! $transaksi->bolehUnggahBukti()) {
            throw ValidationException::withMessages(['bukti_pembayaran' => 'Pembayaran sudah diverifikasi; bukti tidak dapat diganti.']);
        }
        $request->validate(['bukti_pembayaran' => 'required|image|max:2048']);

        if ($transaksi->bukti_pembayaran) {
            Storage::disk('public')->delete($transaksi->bukti_pembayaran);
        }
        $transaksi->update([
            'bukti_pembayaran' => $request->file('bukti_pembayaran')->store('bukti', 'public'),
            'status_bayar' => 'menunggu_verifikasi',
        ]);

        return $this->respond($transaksi->fresh(), 'Bukti pembayaran diunggah. Menunggu verifikasi UMKM.');
    }

    #[OA\Post(path: '/api/transaksi/{transaksi}/terima', tags: ['Transaksi Customer'], summary: 'Tandai pesanan diterima', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'transaksi', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Status selesai')])]
    public function terima(Request $request, Transaksi $transaksi)
    {
        $this->authorize('viewAsCustomer', $transaksi);
        if (! $transaksi->bolehTerima()) {
            throw ValidationException::withMessages(['status' => 'Pesanan belum dikirim.']);
        }
        $transaksi->update(['status' => 'selesai']);

        return $this->respond($transaksi->fresh(), 'Pesanan ditandai diterima.');
    }
}
