<?php

namespace App\Http\Controllers\Api\Umkm;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Umkm\Concerns\ResolvesUmkm;
use App\Services\PrediksiService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PrediksiController extends ApiController
{
    use ResolvesUmkm;

    public function __construct(private PrediksiService $prediksi)
    {
    }

    private function resolveUmkmOr409(Request $request)
    {
        $umkm = $this->umkm($request);
        abort_if(! $umkm, 409, 'Lengkapi profil UMKM dulu.');

        return $umkm;
    }

    #[OA\Get(path: '/api/umkm/prediksi/omzet', tags: ['UMKM Prediksi'], summary: 'Proyeksi omzet 7 hari / 3 bulan ke depan, regresi linear (?periode=7d|30d|12m). Null bila data belum cukup.', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'periode', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['7d', '30d', '12m']))],
        responses: [new OA\Response(response: 200, description: 'forecast: labels/nilai/total/horizon | null')])]
    public function omzet(Request $request)
    {
        $data = $request->validate(['periode' => 'sometimes|in:7d,30d,12m']);
        $umkm = $this->resolveUmkmOr409($request);
        $periode = $data['periode'] ?? '30d';

        return $this->respond([
            'forecast' => $this->prediksi->forecastOmzet($umkm->id, $periode),
            'periode' => $periode,
        ]);
    }

    #[OA\Get(path: '/api/umkm/prediksi/stok-habis', tags: ['UMKM Prediksi'], summary: 'Estimasi produk yang stoknya segera habis dari laju jual 30 hari', security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'rows: nama/stok/laju/hari_tersisa')])]
    public function stokHabis(Request $request)
    {
        $umkm = $this->resolveUmkmOr409($request);

        return $this->respond(['rows' => $this->prediksi->stokHabis($umkm->id)]);
    }

    #[OA\Get(path: '/api/umkm/prediksi/produk-trending', tags: ['UMKM Prediksi'], summary: 'Produk dengan momentum penjualan naik (slope regresi 30 hari)', security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'rows: nama/terjual/slope')])]
    public function produkTrending(Request $request)
    {
        $umkm = $this->resolveUmkmOr409($request);

        return $this->respond(['rows' => $this->prediksi->produkTrending($umkm->id)]);
    }
}
