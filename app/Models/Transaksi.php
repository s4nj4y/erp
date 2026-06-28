<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUmkm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaksi extends Model
{
    use BelongsToUmkm;

    protected $table = 'transaksi';
    protected $guarded = [];
    protected $casts = ['tanggal' => 'datetime'];

    public function customer(): BelongsTo { return $this->belongsTo(User::class, 'customer_id'); }
    public function umkm(): BelongsTo { return $this->belongsTo(Umkm::class, 'umkm_id'); }
    public function bank(): BelongsTo { return $this->belongsTo(Bank::class, 'bank_id'); }
    public function detail(): HasMany { return $this->hasMany(TransaksiDetail::class, 'transaksi_id'); }
}
