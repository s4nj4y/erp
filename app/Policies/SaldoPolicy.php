<?php

namespace App\Policies;

use App\Models\Saldo;
use App\Models\User;
use App\Policies\Concerns\HandlesUmkmOwnership;
use Illuminate\Auth\Access\Response;

class SaldoPolicy
{
    use HandlesUmkmOwnership;

    public function delete(User $user, Saldo $saldo): Response
    {
        return $this->ownsResponse($user, $saldo->umkm_id);
    }
}
