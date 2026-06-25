<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisPengeluaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JenisPengeluaranController extends Controller
{
    public function index(): View
    {
        $items = JenisPengeluaran::latest()->paginate(10);

        return view('admin.jenis-pengeluaran.index', compact('items'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['nama' => 'required|string|max:100']);
        JenisPengeluaran::create($data);

        return back()->with('success', 'Jenis pengeluaran ditambahkan.');
    }

    public function edit(JenisPengeluaran $jenis_pengeluaran): View
    {
        return view('admin.jenis-pengeluaran.edit', ['item' => $jenis_pengeluaran]);
    }

    public function update(Request $request, JenisPengeluaran $jenis_pengeluaran): RedirectResponse
    {
        $data = $request->validate(['nama' => 'required|string|max:100']);
        $jenis_pengeluaran->update($data);

        return redirect()->route('admin.jenis-pengeluaran.index')->with('success', 'Jenis pengeluaran diperbarui.');
    }

    public function destroy(JenisPengeluaran $jenis_pengeluaran): RedirectResponse
    {
        $jenis_pengeluaran->delete();

        return back()->with('success', 'Jenis pengeluaran dihapus.');
    }
}
