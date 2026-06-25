<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeranjangBelanja extends Model
{
    protected $table = 'keranjang_belanja';
    protected $guarded = [];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function produk(): BelongsTo { return $this->belongsTo(Produk::class, 'produk_id'); }
}
