<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisUsaha extends Model
{
    protected $table = 'jenis_usaha';
    protected $guarded = [];

    public function umkm(): HasMany { return $this->hasMany(Umkm::class, 'jenis_usaha_id'); }
}
