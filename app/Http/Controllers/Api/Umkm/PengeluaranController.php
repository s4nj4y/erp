<?php

namespace App\Http\Controllers\Api\Umkm;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Umkm\Concerns\ResolvesUmkm;
use App\Models\TransaksiPengeluaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class PengeluaranController extends ApiController
{
    use ResolvesUmkm;

    #[OA\Get(path: '/api/umkm/pengeluaran', tags: ['UMKM Keuangan'], summary: 'Daftar pengeluaran + total keseluruhan', security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: '{pengeluaran: paginasi, total}'),
            new OA\Response(response: 409, description: 'Belum ada profil UMKM')])]
    public function index(Request $request)
    {
        $umkm = $this->umkm($request);
        abort_if(! $umkm, 409, 'Lengkapi profil UMKM dulu.');

        $pengeluaran = TransaksiPengeluaran::with('jenis')
            ->withCount('detail')
            ->forUmkm($umkm)
            ->orderByDesc('tanggal_pengeluaran')
            ->paginate(10);

        return $this->respond([
            'pengeluaran' => $pengeluaran,
            'total' => (int) TransaksiPengeluaran::forUmkm($umkm)->sum('total_harga'),
        ]);
    }

    #[OA\Post(path: '/api/umkm/pengeluaran', tags: ['UMKM Keuangan'], summary: 'Catat pengeluaran (multi item)', security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['tanggal_pengeluaran', 'items'], properties: [
                new OA\Property(property: 'jenis_pengeluaran_id', type: 'integer', nullable: true),
                new OA\Property(property: 'tanggal_pengeluaran', type: 'string', format: 'date'),
                new OA\Property(property: 'items', type: 'array', items: new OA\Items(properties: [
                    new OA\Property(property: 'keterangan', type: 'string'),
                    new OA\Property(property: 'qty', type: 'integer'),
                    new OA\Property(property: 'harga', type: 'integer')]))])),
        responses: [new OA\Response(response: 201, description: 'Pengeluaran + detail tersimpan')])]
    public function store(Request $request)
    {
        $umkm = $this->umkm($request);
        abort_if(! $umkm, 409, 'Lengkapi profil UMKM dulu.');

        $data = $request->validate([
            'jenis_pengeluaran_id' => ['nullable', Rule::exists('jenis_pengeluaran', 'id')],
            'tanggal_pengeluaran' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.keterangan' => 'required|string|max:200',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.harga' => 'required|integer|min:0',
        ]);

        $pengeluaran = DB::transaction(function () use ($umkm, $data) {
            $total = collect($data['items'])->sum(fn ($i) => $i['qty'] * $i['harga']);

            $pengeluaran = TransaksiPengeluaran::create([
                'umkm_id' => $umkm->id,
                'jenis_pengeluaran_id' => $data['jenis_pengeluaran_id'] ?? null,
                'tanggal_pengeluaran' => $data['tanggal_pengeluaran'],
                'total_harga' => $total,
            ]);

            foreach ($data['items'] as $i) {
                $pengeluaran->detail()->create([
                    'keterangan' => $i['keterangan'],
                    'qty' => $i['qty'],
                    'harga' => $i['harga'],
                    'sub_total' => $i['qty'] * $i['harga'],
                ]);
            }

            return $pengeluaran;
        });

        return $this->respond($pengeluaran->load('jenis', 'detail'), 'Pengeluaran dicatat.', 201);
    }

    #[OA\Get(path: '/api/umkm/pengeluaran/{pengeluaran}', tags: ['UMKM Keuangan'], summary: 'Detail pengeluaran', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'pengeluaran', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Pengeluaran + jenis + detail'),
            new OA\Response(response: 404, description: 'Bukan milik toko user')])]
    public function show(Request $request, TransaksiPengeluaran $pengeluaran)
    {
        $this->authorize('view', $pengeluaran);

        return $this->respond($pengeluaran->load('jenis', 'detail'));
    }

    #[OA\Delete(path: '/api/umkm/pengeluaran/{pengeluaran}', tags: ['UMKM Keuangan'], summary: 'Hapus pengeluaran', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'pengeluaran', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Terhapus')])]
    public function destroy(Request $request, TransaksiPengeluaran $pengeluaran)
    {
        $this->authorize('delete', $pengeluaran);
        $pengeluaran->delete();

        return $this->respond(message: 'Pengeluaran dihapus.');
    }
}
