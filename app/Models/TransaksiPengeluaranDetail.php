<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiPengeluaranDetail extends Model
{
    protected $table = 'transaksi_pengeluaran_detail';
    protected $guarded = [];

    public function pengeluaran(): BelongsTo { return $this->belongsTo(TransaksiPengeluaran::class, 'transaksi_pengeluaran_id'); }
}
