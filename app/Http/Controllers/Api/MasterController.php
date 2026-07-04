<?php

namespace App\Http\Controllers\Api;

use App\Models\Bank;
use App\Models\JenisUsaha;
use App\Models\KategoriProduk;
use OpenApi\Attributes as OA;

class MasterController extends ApiController
{
    #[OA\Get(path: '/api/master/jenis-usaha', tags: ['Master'], summary: 'Daftar jenis usaha', security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Array jenis usaha')])]
    public function jenisUsaha()
    {
        return $this->respond(JenisUsaha::orderBy('nama_usaha')->get());
    }

    #[OA\Get(path: '/api/master/bank', tags: ['Master'], summary: 'Daftar bank', security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Array bank')])]
    public function bank()
    {
        return $this->respond(Bank::orderBy('nama_bank')->get());
    }

    #[OA\Get(path: '/api/master/kategori-produk', tags: ['Master'], summary: 'Daftar kategori produk', security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Array kategori')])]
    public function kategoriProduk()
    {
        return $this->respond(KategoriProduk::orderBy('nama')->get());
    }
}
