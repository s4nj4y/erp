<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeuanganUmkm extends Model
{
    protected $table = 'keuangan_umkm';
    protected $guarded = [];
    protected $casts = ['tanggal_transaksi' => 'date', 'debit' => 'decimal:2', 'kredit' => 'decimal:2', 'saldo' => 'decimal:2'];

    public function umkm(): BelongsTo { return $this->belongsTo(Umkm::class, 'umkm_id'); }
}
