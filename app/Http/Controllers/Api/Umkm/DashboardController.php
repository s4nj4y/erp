<?php

namespace App\Http\Controllers\Api\Umkm;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Umkm\Concerns\ResolvesUmkm;
use App\Models\Produk;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DashboardController extends ApiController
{
    use ResolvesUmkm;

    #[OA\Get(path: '/api/umkm/dashboard', tags: ['UMKM'], summary: 'Ringkasan dashboard penjual', security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'umkm (null bila belum ada) + stats')])]
    public function index(Request $request)
    {
        $umkm = $this->umkm($request);

        $stats = ['produk' => 0, 'pesanan' => 0, 'perlu_verifikasi' => 0, 'pendapatan' => 0];

        if ($umkm) {
            $stats['produk'] = Produk::where('umkm_id', $umkm->id)->count();
            $stats['pesanan'] = Transaksi::where('umkm_id', $umkm->id)->count();
            $stats['perlu_verifikasi'] = Transaksi::where('umkm_id', $umkm->id)
                ->where('status_bayar', 'menunggu_verifikasi')->count();
            $stats['pendapatan'] = Transaksi::where('umkm_id', $umkm->id)
                ->where('status', 'selesai')
                ->with('detail')->get()
                ->sum(fn ($t) => $t->detail->sum(fn ($d) => $d->harga * $d->qty));
        }

        return $this->respond(['umkm' => $umkm, 'stats' => $stats]);
    }
}
