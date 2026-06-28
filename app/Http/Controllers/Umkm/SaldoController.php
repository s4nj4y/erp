<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Umkm\Concerns\ResolvesUmkm;
use App\Models\Saldo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaldoController extends Controller
{
    use ResolvesUmkm;

    public function index(Request $request): View|RedirectResponse
    {
        $umkm = $this->umkm($request);
        if (! $umkm) {
            return redirect()->route('umkm.profil.edit')->with('success', 'Lengkapi profil UMKM dulu.');
        }

        $saldo = Saldo::forUmkm($umkm)
            ->orderByDesc('tanggal_transaksi')->orderByDesc('id')
            ->paginate(15);

        $modalSaatIni = $this->currentSaldo($umkm->id);

        return view('umkm.keuangan.saldo', compact('saldo', 'modalSaatIni'));
    }

    public function store(Request $request): RedirectResponse
    {
        $umkm = $this->umkm($request);
        abort_if(! $umkm, 409);

        $data = $request->validate([
            'tanggal_transaksi' => 'required|date',
            'jenis_transaksi' => 'required|in:investasi_awal,penambahan_modal,pengambilan_modal',
            'keterangan' => 'nullable|string|max:255',
            'jumlah' => 'required|integer|min:1',
        ]);
        $data['umkm_id'] = $umkm->id;
        $data['saldo'] = 0;
        Saldo::create($data);

        $this->recalculate($umkm->id);

        return back()->with('success', 'Catatan modal disimpan.');
    }

    public function destroy(Request $request, Saldo $saldo): RedirectResponse
    {
        $this->authorize('delete', $saldo);
        $umkmId = $saldo->umkm_id;
        $saldo->delete();
        $this->recalculate($umkmId);

        return back()->with('success', 'Catatan modal dihapus.');
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
