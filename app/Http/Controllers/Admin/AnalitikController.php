<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalitikService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalitikController extends Controller
{
    public function index(Request $request, AnalitikService $analitik): View
    {
        $periode = $request->query('periode');
        if (! in_array($periode, AnalitikService::PERIODE, true)) {
            $periode = '30d';
        }

        $pertumbuhan = $analitik->pertumbuhan($periode);
        $umkmTeratas = $analitik->umkmTeratas($periode);

        return view('admin.analitik', compact('periode', 'pertumbuhan', 'umkmTeratas'));
    }
}
