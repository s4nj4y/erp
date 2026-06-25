<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stok extends Model
{
    protected $table = 'stok';
    protected $guarded = [];
    protected $casts = ['tanggal' => 'date'];

    public function produk(): BelongsTo { return $this->belongsTo(Produk::class, 'produk_id'); }
}
