<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalitikService;
use App\Services\PrediksiService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalitikController extends Controller
{
    public function index(Request $request, AnalitikService $analitik, PrediksiService $prediksiSvc): View
    {
        $periode = $request->query('periode');
        if (! in_array($periode, AnalitikService::PERIODE, true)) {
            $periode = '30d';
        }

        $pertumbuhan = $analitik->pertumbuhan($periode);
        $umkmTeratas = $analitik->umkmTeratas($periode);
        $prediksi = $prediksiSvc->forecastPertumbuhan($periode);

        return view('admin.analitik', compact('periode', 'pertumbuhan', 'umkmTeratas', 'prediksi'));
    }
}
