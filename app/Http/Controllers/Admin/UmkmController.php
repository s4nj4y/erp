<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisUsaha;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UmkmController extends Controller
{
    public function index(Request $request): View
    {
        $umkm = Umkm::with('jenisUsaha')
            ->withCount('produk')
            ->when($request->q, fn ($q) => $q->where('nama_umkm', 'like', '%'.$request->q.'%'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.umkm.index', compact('umkm'));
    }

    public function create(): View
    {
        return view('admin.umkm.create', [
            'jenisUsaha' => JenisUsaha::orderBy('nama_usaha')->get(),
            'users' => User::where('role', 'umkm')->whereDoesntHave('umkm')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['foto'] = $this->handleFoto($request);
        $data['status'] = $request->boolean('status');
        Umkm::create($data);

        return redirect()->route('admin.umkm.index')->with('success', 'UMKM ditambahkan.');
    }

    public function edit(Umkm $umkm): View
    {
        $umkm->load('rekening.bank');

        return view('admin.umkm.edit', [
            'umkm' => $umkm,
            'jenisUsaha' => JenisUsaha::orderBy('nama_usaha')->get(),
            'users' => User::where('role', 'umkm')
                ->where(fn ($q) => $q->whereDoesntHave('umkm')->orWhere('id', $umkm->user_id))
                ->get(),
            'banks' => \App\Models\Bank::orderBy('nama_bank')->get(),
        ]);
    }

    public function update(Request $request, Umkm $umkm): RedirectResponse
    {
        $data = $this->validateData($request, $umkm);
        if ($foto = $this->handleFoto($request)) {
            if ($umkm->foto) {
                Storage::disk('public')->delete($umkm->foto);
            }
            $data['foto'] = $foto;
        }
        $data['status'] = $request->boolean('status');
        $umkm->update($data);

        return redirect()->route('admin.umkm.index')->with('success', 'UMKM diperbarui.');
    }

    public function destroy(Umkm $umkm): RedirectResponse
    {
        if ($umkm->foto) {
            Storage::disk('public')->delete($umkm->foto);
        }
        $umkm->delete();

        return back()->with('success', 'UMKM dihapus.');
    }

    public function toggleStatus(Umkm $umkm): RedirectResponse
    {
        $umkm->update(['status' => ! $umkm->status]);

        return back()->with('success', 'Status UMKM diperbarui.');
    }

    private function validateData(Request $request, ?Umkm $umkm = null): array
    {
        return $request->validate([
            'user_id' => ['nullable', Rule::exists('users', 'id')],
            'nama_umkm' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'no_wa' => 'nullable|string|max:20',
            'alamat' => 'nullable|string|max:150',
            'deskripsi' => 'nullable|string|max:255',
            'tgl_pendirian' => 'nullable|date',
            'nama_pendiri' => 'nullable|string|max:100',
            'jenis_usaha_id' => ['nullable', Rule::exists('jenis_usaha', 'id')],
            'status' => 'boolean',
            'foto' => 'nullable|image|max:2048',
        ]);
    }

    private function handleFoto(Request $request): ?string
    {
        if ($request->hasFile('foto')) {
            return $request->file('foto')->store('umkm', 'public');
        }

        return null;
    }
}
