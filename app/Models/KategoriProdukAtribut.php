<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriProdukAtribut extends Model
{
    protected $table = 'kategori_produk_atribut';
    protected $guarded = [];

    public function kategori(): BelongsTo { return $this->belongsTo(KategoriProduk::class, 'kategori_produk_id'); }
    public function detail(): HasMany { return $this->hasMany(ProdukDetail::class, 'atribut_id'); }
}
