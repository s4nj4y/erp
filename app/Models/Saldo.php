<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUmkm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Saldo extends Model
{
    use BelongsToUmkm;

    protected $table = 'saldo';
    protected $guarded = [];
    protected $casts = ['tanggal_transaksi' => 'date'];

    public function umkm(): BelongsTo { return $this->belongsTo(Umkm::class, 'umkm_id'); }
}
