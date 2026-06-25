<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BankController extends Controller
{
    public function index(): View
    {
        $items = Bank::latest()->paginate(10);

        return view('admin.bank.index', compact('items'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['nama_bank' => 'required|string|max:60']);
        Bank::create($data);

        return back()->with('success', 'Bank ditambahkan.');
    }

    public function edit(Bank $bank): View
    {
        return view('admin.bank.edit', ['item' => $bank]);
    }

    public function update(Request $request, Bank $bank): RedirectResponse
    {
        $data = $request->validate(['nama_bank' => 'required|string|max:60']);
        $bank->update($data);

        return redirect()->route('admin.bank.index')->with('success', 'Bank diperbarui.');
    }

    public function destroy(Bank $bank): RedirectResponse
    {
        $bank->delete();

        return back()->with('success', 'Bank dihapus.');
    }
}
