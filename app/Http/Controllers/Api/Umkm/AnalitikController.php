<?php

namespace App\Http\Controllers\Api\Umkm;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Umkm\Concerns\ResolvesUmkm;
use App\Services\AnalitikService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AnalitikController extends ApiController
{
    use ResolvesUmkm;

    public function __construct(private AnalitikService $analitik) {}

    /** Validasi ?periode= lalu resolve UMKM (409 bila profil belum ada). */
    private function siapkan(Request $request): array
    {
        $data = $request->validate(['periode' => 'sometimes|in:7d,30d,12m']);
        $umkm = $this->umkm($request);
        abort_if(! $umkm, 409, 'Lengkapi profil UMKM dulu.');

        return [$umkm, $data['periode'] ?? '30d'];
    }

    #[OA\Get(path: '/api/umkm/analitik/tren', tags: ['UMKM Analitik'], summary: 'Tren omzet & jumlah transaksi per hari/bulan (?periode=7d|30d|12m, default 30d)', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'periode', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['7d', '30d', '12m']))],
        responses: [new OA\Response(response: 200, description: 'labels/omzet/transaksi/total_omzet/total_transaksi')])]
    public function tren(Request $request)
    {
        [$umkm, $periode] = $this->siapkan($request);

        return $this->respond($this->analitik->tren($umkm->id, $periode) + ['periode' => $periode]);
    }

    #[OA\Get(path: '/api/umkm/analitik/produk-terlaris', tags: ['UMKM Analitik'], summary: 'Top 10 produk berdasarkan nilai penjualan (?periode=)', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'periode', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['7d', '30d', '12m']))],
        responses: [new OA\Response(response: 200, description: 'rows: nama/terjual/nilai')])]
    public function produkTerlaris(Request $request)
    {
        [$umkm, $periode] = $this->siapkan($request);

        return $this->respond([
            'rows' => $this->analitik->produkTerlaris($umkm->id, $periode),
            'periode' => $periode,
        ]);
    }

    #[OA\Get(path: '/api/umkm/analitik/pelanggan', tags: ['UMKM Analitik'], summary: 'Top pelanggan + pelanggan baru vs lama (?periode=)', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'periode', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['7d', '30d', '12m']))],
        responses: [new OA\Response(response: 200, description: 'top: nama/transaksi/belanja + pelanggan_baru/pelanggan_lama')])]
    public function pelanggan(Request $request)
    {
        [$umkm, $periode] = $this->siapkan($request);

        return $this->respond($this->analitik->pelanggan($umkm->id, $periode) + ['periode' => $periode]);
    }
}
