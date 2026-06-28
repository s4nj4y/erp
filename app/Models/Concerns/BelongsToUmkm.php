<?php

namespace App\Models\Concerns;

use App\Models\Umkm;
use Illuminate\Database\Eloquent\Builder;

/**
 * Untuk model yang punya kolom umkm_id. Menyediakan scope listing & accessor
 * pemilik yang dipakai oleh policy.
 */
trait BelongsToUmkm
{
    public function scopeForUmkm(Builder $query, Umkm $umkm): Builder
    {
        return $query->where('umkm_id', $umkm->id);
    }

    public function ownerUmkmId(): ?int
    {
        return $this->umkm_id;
    }
}
