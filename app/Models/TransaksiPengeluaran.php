<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransaksiPengeluaran extends Model
{
    protected $table = 'transaksi_pengeluaran';
    protected $guarded = [];
    protected $casts = ['tanggal_pengeluaran' => 'datetime'];

    public function umkm(): BelongsTo { return $this->belongsTo(Umkm::class, 'umkm_id'); }
    public function jenis(): BelongsTo { return $this->belongsTo(JenisPengeluaran::class, 'jenis_pengeluaran_id'); }
    public function detail(): HasMany { return $this->hasMany(TransaksiPengeluaranDetail::class, 'transaksi_pengeluaran_id'); }
}
