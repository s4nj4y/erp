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
