<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisUsaha;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JenisUsahaController extends Controller
{
    public function index(): View
    {
        $items = JenisUsaha::latest()->paginate(10);

        return view('admin.jenis-usaha.index', compact('items'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['nama_usaha' => 'required|string|max:100']);
        JenisUsaha::create($data);

        return back()->with('success', 'Jenis usaha ditambahkan.');
    }

    public function edit(JenisUsaha $jenis_usaha): View
    {
        return view('admin.jenis-usaha.edit', ['item' => $jenis_usaha]);
    }

    public function update(Request $request, JenisUsaha $jenis_usaha): RedirectResponse
    {
        $data = $request->validate(['nama_usaha' => 'required|string|max:100']);
        $jenis_usaha->update($data);

        return redirect()->route('admin.jenis-usaha.index')->with('success', 'Jenis usaha diperbarui.');
    }

    public function destroy(JenisUsaha $jenis_usaha): RedirectResponse
    {
        $jenis_usaha->delete();

        return back()->with('success', 'Jenis usaha dihapus.');
    }
}
