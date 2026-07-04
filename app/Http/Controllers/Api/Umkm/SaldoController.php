<?php

namespace App\Http\Controllers\Api\Umkm;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Umkm\Concerns\ResolvesUmkm;
use App\Models\Saldo;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class SaldoController extends ApiController
{
    use ResolvesUmkm;

    #[OA\Get(path: '/api/umkm/saldo', tags: ['UMKM Keuangan'], summary: 'Riwayat modal + modal saat ini', security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: '{saldo: paginasi, modal_saat_ini}'),
            new OA\Response(response: 409, description: 'Belum ada profil UMKM')])]
    public function index(Request $request)
    {
        $umkm = $this->umkm($request);
        abort_if(! $umkm, 409, 'Lengkapi profil UMKM dulu.');

        $saldo = Saldo::forUmkm($umkm)
            ->orderByDesc('tanggal_transaksi')->orderByDesc('id')
            ->paginate(15);

        return $this->respond([
            'saldo' => $saldo,
            'modal_saat_ini' => $this->currentSaldo($umkm->id),
        ]);
    }

    #[OA\Post(path: '/api/umkm/saldo', tags: ['UMKM Keuangan'], summary: 'Catat mutasi modal', security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['tanggal_transaksi', 'jenis_transaksi', 'jumlah'], properties: [
                new OA\Property(property: 'tanggal_transaksi', type: 'string', format: 'date'),
                new OA\Property(property: 'jenis_transaksi', type: 'string', enum: ['investasi_awal', 'penambahan_modal', 'pengambilan_modal']),
                new OA\Property(property: 'jumlah', type: 'integer'),
                new OA\Property(property: 'keterangan', type: 'string', nullable: true)])),
        responses: [new OA\Response(response: 201, description: 'Tersimpan, saldo berjalan dihitung ulang')])]
    public function store(Request $request)
    {
        $umkm = $this->umkm($request);
        abort_if(! $umkm, 409, 'Lengkapi profil UMKM dulu.');

        $data = $request->validate([
            'tanggal_transaksi' => 'required|date',
            'jenis_transaksi' => 'required|in:investasi_awal,penambahan_modal,pengambilan_modal',
            'keterangan' => 'nullable|string|max:255',
            'jumlah' => 'required|integer|min:1',
        ]);
        $data['umkm_id'] = $umkm->id;
        $data['saldo'] = 0;
        $saldo = Saldo::create($data);

        $this->recalculate($umkm->id);

        return $this->respond($saldo->fresh(), 'Catatan modal disimpan.', 201);
    }

    #[OA\Delete(path: '/api/umkm/saldo/{saldo}', tags: ['UMKM Keuangan'], summary: 'Hapus catatan modal', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'saldo', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Terhapus, saldo dihitung ulang')])]
    public function destroy(Request $request, Saldo $saldo)
    {
        $this->authorize('delete', $saldo);
        $umkmId = $saldo->umkm_id;
        $saldo->delete();
        $this->recalculate($umkmId);

        return $this->respond(message: 'Catatan modal dihapus.');
    }

    /** Hitung ulang kolom saldo berjalan secara kronologis. */
    private function recalculate(int $umkmId): void
    {
        $running = 0;
        Saldo::where('umkm_id', $umkmId)
            ->orderBy('tanggal_transaksi')->orderBy('id')
            ->get()
            ->each(function (Saldo $s) use (&$running) {
                $running += $s->jenis_transaksi === 'pengambilan_modal' ? -$s->jumlah : $s->jumlah;
                $s->updateQuietly(['saldo' => $running]);
            });
    }

    private function currentSaldo(int $umkmId): int
    {
        $row = Saldo::where('umkm_id', $umkmId)
            ->selectRaw("COALESCE(SUM(CASE WHEN jenis_transaksi = 'pengambilan_modal' THEN -jumlah ELSE jumlah END), 0) as total")
            ->first();

        return (int) ($row->total ?? 0);
    }
}
