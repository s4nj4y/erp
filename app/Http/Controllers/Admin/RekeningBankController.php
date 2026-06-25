<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RekeningBank;
use App\Models\Umkm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RekeningBankController extends Controller
{
    public function store(Request $request, Umkm $umkm): RedirectResponse
    {
        $data = $request->validate([
            'bank_id' => ['required', Rule::exists('bank', 'id')],
            'atas_nama' => 'required|string|max:100',
            'rekening' => 'required|string|max:60',
            'status' => 'boolean',
        ]);
        $data['status'] = $request->boolean('status', true);
        $umkm->rekening()->create($data);

        return back()->with('success', 'Rekening ditambahkan.');
    }

    public function destroy(RekeningBank $rekening): RedirectResponse
    {
        $rekening->delete();

        return back()->with('success', 'Rekening dihapus.');
    }
}
