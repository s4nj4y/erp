<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisPengeluaran extends Model
{
    protected $table = 'jenis_pengeluaran';
    protected $guarded = [];

    public function pengeluaran(): HasMany { return $this->hasMany(TransaksiPengeluaran::class, 'jenis_pengeluaran_id'); }
}
