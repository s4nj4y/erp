<?php

namespace App\Http\Controllers\Api\Umkm;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Umkm\Concerns\ResolvesUmkm;
use App\Models\Stok;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class TransaksiController extends ApiController
{
    use ResolvesUmkm;

    #[OA\Get(path: '/api/umkm/transaksi', tags: ['UMKM Transaksi'], summary: 'Pesanan masuk (filter ?status= status_bayar)', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Paginasi transaksi'),
            new OA\Response(response: 409, description: 'Belum ada profil UMKM')])]
    public function index(Request $request)
    {
        $umkm = $this->umkm($request);
        abort_if(! $umkm, 409, 'Lengkapi profil UMKM dulu.');

        $transaksi = Transaksi::with('customer')
            ->withCount('detail')
            ->forUmkm($umkm)
            ->when($request->status, fn ($q) => $q->where('status_bayar', $request->status))
            ->latest('tanggal')
            ->paginate(10);

        return $this->respond($transaksi);
    }

    #[OA\Get(path: '/api/umkm/transaksi/{transaksi}', tags: ['UMKM Transaksi'], summary: 'Detail pesanan + total', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'transaksi', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Detail + total'),
            new OA\Response(response: 404, description: 'Bukan pesanan toko user')])]
    public function show(Request $request, Transaksi $transaksi)
    {
        $this->authorize('viewAsUmkm', $transaksi);
        $transaksi->load('customer', 'bank', 'detail.produk');
        $transaksi->detail->each(fn ($d) => $d->produk?->append('gambar_url'));
        $transaksi->append('bukti_pembayaran_url');

        return $this->respond([
            'transaksi' => $transaksi,
            'total' => $transaksi->detail->sum(fn ($d) => $d->harga * $d->qty),
        ]);
    }

    #[OA\Post(path: '/api/umkm/transaksi/{transaksi}/verifikasi', tags: ['UMKM Transaksi'], summary: 'Verifikasi pembayaran → diproses', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'transaksi', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Terverifikasi'),
            new OA\Response(response: 422, description: 'Status pembayaran sudah bukan menunggu_verifikasi')])]
    public function verifikasi(Request $request, Transaksi $transaksi)
    {
        $this->authorize('manage', $transaksi);

        DB::transaction(function () use ($transaksi) {
            $terkunci = Transaksi::whereKey($transaksi->id)->lockForUpdate()->first();
            if (! $terkunci->bolehVerifikasi()) {
                throw ValidationException::withMessages(['status_bayar' => 'Pembayaran sudah diproses sebelumnya.']);
            }
            $terkunci->update(['status_bayar' => 'terverifikasi', 'status' => 'diproses']);
        });

        return $this->respond($transaksi->fresh(), 'Pembayaran diverifikasi. Pesanan diproses.');
    }

    #[OA\Post(path: '/api/umkm/transaksi/{transaksi}/tolak', tags: ['UMKM Transaksi'], summary: 'Tolak pembayaran → stok dikembalikan', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'transaksi', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Ditolak, stok kembali'),
            new OA\Response(response: 422, description: 'Status pembayaran sudah bukan menunggu_verifikasi')])]
    public function tolak(Request $request, Transaksi $transaksi)
    {
        $this->authorize('manage', $transaksi);

        DB::transaction(function () use ($transaksi) {
            $terkunci = Transaksi::whereKey($transaksi->id)->lockForUpdate()->first();
            if (! $terkunci->bolehTolak()) {
                throw ValidationException::withMessages(['status_bayar' => 'Pembayaran sudah diproses sebelumnya.']);
            }

            $terkunci->loadMissing('detail.produk');
            foreach ($terkunci->detail as $d) {
                if ($d->produk) {
                    $d->produk->increment('stok', $d->qty);
                    Stok::create([
                        'produk_id' => $d->produk_id,
                        'status' => 'masuk',
                        'jumlah_masuk' => $d->qty,
                        'jumlah_keluar' => 0,
                        'tanggal' => now()->toDateString(),
                        'keterangan' => 'Pembatalan '.$terkunci->kode_transaksi,
                    ]);
                }
            }
            $terkunci->update(['status_bayar' => 'ditolak', 'status' => 'dibatalkan']);
        });

        return $this->respond($transaksi->fresh(), 'Pembayaran ditolak. Stok dikembalikan.');
    }

    #[OA\Post(path: '/api/umkm/transaksi/{transaksi}/kirim', tags: ['UMKM Transaksi'], summary: 'Tandai pesanan dikirim', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'transaksi', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Dikirim'),
            new OA\Response(response: 422, description: 'Status pesanan bukan diproses')])]
    public function kirim(Request $request, Transaksi $transaksi)
    {
        $this->authorize('manage', $transaksi);

        DB::transaction(function () use ($transaksi) {
            $terkunci = Transaksi::whereKey($transaksi->id)->lockForUpdate()->first();
            if (! $terkunci->bolehKirim()) {
                throw ValidationException::withMessages(['status' => 'Pesanan belum siap dikirim.']);
            }
            $terkunci->update(['status' => 'dikirim']);
        });

        return $this->respond($transaksi->fresh(), 'Pesanan ditandai dikirim.');
    }
}
