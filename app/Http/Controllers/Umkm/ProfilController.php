<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Umkm\Concerns\ResolvesUmkm;
use App\Models\Bank;
use App\Models\JenisUsaha;
use App\Models\RekeningBank;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfilController extends Controller
{
    use ResolvesUmkm;

    public function edit(Request $request): View
    {
        $umkm = $this->umkm($request);
        $umkm?->load('rekening.bank');

        return view('umkm.profil', [
            'umkm' => $umkm,
            'jenisUsaha' => JenisUsaha::orderBy('nama_usaha')->get(),
            'banks' => Bank::orderBy('nama_bank')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_umkm' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'no_wa' => 'nullable|string|max:20',
            'alamat' => 'nullable|string|max:150',
            'deskripsi' => 'nullable|string|max:255',
            'tgl_pendirian' => 'nullable|date',
            'nama_pendiri' => 'nullable|string|max:100',
            'jenis_usaha_id' => ['nullable', Rule::exists('jenis_usaha', 'id')],
            'foto' => 'nullable|image|max:2048',
        ]);

        $umkm = $this->umkm($request);

        if ($request->hasFile('foto')) {
            if ($umkm?->foto) {
                Storage::disk('public')->delete($umkm->foto);
            }
            $data['foto'] = $request->file('foto')->store('umkm', 'public');
        }

        if ($umkm) {
            $umkm->update($data);
        } else {
            $data['user_id'] = $request->user()->id;
            $data['status'] = true;
            $request->user()->umkm()->create($data);
        }

        return back()->with('success', 'Profil UMKM disimpan.');
    }

    public function storeRekening(Request $request): RedirectResponse
    {
        $umkm = $this->umkm($request);
        abort_if(! $umkm, 409, 'Lengkapi profil UMKM dulu.');

        $data = $request->validate([
            'bank_id' => ['required', Rule::exists('bank', 'id')],
            'atas_nama' => 'required|string|max:100',
            'rekening' => 'required|string|max:60',
        ]);
        $data['status'] = true;
        $umkm->rekening()->create($data);

        return back()->with('success', 'Rekening ditambahkan.');
    }

    public function destroyRekening(Request $request, RekeningBank $rekening): RedirectResponse
    {
        $this->authorize('delete', $rekening);
        $rekening->delete();

        return back()->with('success', 'Rekening dihapus.');
    }
}
