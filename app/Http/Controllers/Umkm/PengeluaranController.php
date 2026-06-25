<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Umkm\Concerns\ResolvesUmkm;
use App\Models\JenisPengeluaran;
use App\Models\TransaksiPengeluaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PengeluaranController extends Controller
{
    use ResolvesUmkm;

    public function index(Request $request): View|RedirectResponse
    {
        $umkm = $this->umkm($request);
        if (! $umkm) {
            return redirect()->route('umkm.profil.edit')->with('success', 'Lengkapi profil UMKM dulu.');
        }

        $pengeluaran = TransaksiPengeluaran::with('jenis')
            ->withCount('detail')
            ->where('umkm_id', $umkm->id)
            ->orderByDesc('tanggal_pengeluaran')
            ->paginate(10);

        $total = TransaksiPengeluaran::where('umkm_id', $umkm->id)->sum('total_harga');

        return view('umkm.keuangan.pengeluaran.index', compact('pengeluaran', 'total'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (! $this->umkm($request)) {
            return redirect()->route('umkm.profil.edit')->with('success', 'Lengkapi profil UMKM dulu.');
        }

        return view('umkm.keuangan.pengeluaran.create', [
            'jenisList' => JenisPengeluaran::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $umkm = $this->umkm($request);
        abort_if(! $umkm, 409);

        $data = $request->validate([
            'jenis_pengeluaran_id' => ['nullable', Rule::exists('jenis_pengeluaran', 'id')],
            'tanggal_pengeluaran' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.keterangan' => 'required|string|max:200',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.harga' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($umkm, $data) {
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
        });

        return redirect()->route('umkm.pengeluaran.index')->with('success', 'Pengeluaran dicatat.');
    }

    public function show(Request $request, TransaksiPengeluaran $pengeluaran): View
    {
        abort_unless($pengeluaran->umkm_id === $this->umkm($request)?->id, 403);
        $pengeluaran->load('jenis', 'detail');

        return view('umkm.keuangan.pengeluaran.show', compact('pengeluaran'));
    }

    public function destroy(Request $request, TransaksiPengeluaran $pengeluaran): RedirectResponse
    {
        abort_unless($pengeluaran->umkm_id === $this->umkm($request)?->id, 403);
        $pengeluaran->delete();

        return back()->with('success', 'Pengeluaran dihapus.');
    }
}
