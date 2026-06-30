<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUmkm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Produk extends Model
{
    use BelongsToUmkm;

    protected $table = 'produk';
    protected $guarded = [];
    protected $casts = ['show' => 'boolean'];

    /** URL gambar: dukung path upload di storage maupun URL absolut (mis. seeder demo). */
    public function getGambarUrlAttribute(): ?string
    {
        if (! $this->gambar) {
            return null;
        }

        return Str::startsWith($this->gambar, ['http://', 'https://'])
            ? $this->gambar
            : asset('storage/'.$this->gambar);
    }

    public function umkm(): BelongsTo { return $this->belongsTo(Umkm::class, 'umkm_id'); }
    public function kategori(): BelongsTo { return $this->belongsTo(KategoriProduk::class, 'kategori_produk_id'); }
    public function detail(): HasMany { return $this->hasMany(ProdukDetail::class, 'produk_id'); }

    // Catatan: dinamai 'stoks' agar tidak bentrok dengan kolom integer 'stok'.
    public function stoks(): HasMany { return $this->hasMany(Stok::class, 'produk_id'); }
}
