<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produk extends Model
{
    protected $table = 'produk';
    protected $guarded = [];
    protected $casts = ['show' => 'boolean'];

    public function umkm(): BelongsTo { return $this->belongsTo(Umkm::class, 'umkm_id'); }
    public function kategori(): BelongsTo { return $this->belongsTo(KategoriProduk::class, 'kategori_produk_id'); }
    public function detail(): HasMany { return $this->hasMany(ProdukDetail::class, 'produk_id'); }
    public function stok(): HasMany { return $this->hasMany(Stok::class, 'produk_id'); }
}
