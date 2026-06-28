<?php

namespace App\Policies;

use App\Models\Stok;
use App\Models\User;
use App\Policies\Concerns\HandlesUmkmOwnership;
use Illuminate\Auth\Access\Response;

class StokPolicy
{
    use HandlesUmkmOwnership;

    public function delete(User $user, Stok $stok): Response
    {
        return $this->ownsResponse($user, $stok->produk?->umkm_id);
    }
}
