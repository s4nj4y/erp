<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Umkm extends Model
{
    protected $table = 'umkm';
    protected $guarded = [];

    protected $casts = ['status' => 'boolean', 'tgl_pendirian' => 'date'];

    protected static function booted(): void
    {
        // Slug dibuat sekali saat create dan stabil selamanya (URL yang tersebar tidak mati).
        static::creating(function (Umkm $umkm) {
            $umkm->slug = $umkm->slug ?: static::uniqueSlug($umkm->nama_umkm);
        });
    }

    /** Slug unik dari nama toko; tabrakan diberi akhiran -2, -3, dst. */
    public static function uniqueSlug(string $nama): string
    {
        $base = Str::slug($nama) ?: 'toko';
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    /** URL foto: dukung path upload di storage maupun URL absolut. */
    public function getFotoUrlAttribute(): ?string
    {
        if (! $this->foto) {
            return null;
        }

        return Str::startsWith($this->foto, ['http://', 'https://'])
            ? $this->foto
            : asset('storage/'.$this->foto);
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function jenisUsaha(): BelongsTo { return $this->belongsTo(JenisUsaha::class, 'jenis_usaha_id'); }
    public function produk(): HasMany { return $this->hasMany(Produk::class, 'umkm_id'); }
    public function rekening(): HasMany { return $this->hasMany(RekeningBank::class, 'umkm_id'); }
    public function transaksi(): HasMany { return $this->hasMany(Transaksi::class, 'umkm_id'); }
    public function saldo(): HasMany { return $this->hasMany(Saldo::class, 'umkm_id'); }
    public function pengeluaran(): HasMany { return $this->hasMany(TransaksiPengeluaran::class, 'umkm_id'); }
}
