<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProdukDetail extends Model
{
    protected $table = 'produk_detail';
    protected $guarded = [];

    public function produk(): BelongsTo { return $this->belongsTo(Produk::class, 'produk_id'); }
    public function atribut(): BelongsTo { return $this->belongsTo(KategoriProdukAtribut::class, 'atribut_id'); }
}
