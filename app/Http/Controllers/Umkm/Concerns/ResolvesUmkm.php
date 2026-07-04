<?php

namespace App\Http\Controllers\Umkm\Concerns;

use App\Models\Umkm;
use Illuminate\Http\Request;

trait ResolvesUmkm
{
    /** Ambil UMKM milik user yang login. Null bila belum dibuat. */
    protected function umkm(Request $request): ?Umkm
    {
        return $request->user()->umkm()->first();
    }
}
