<?php

namespace App\Policies;

use App\Models\RekeningBank;
use App\Models\User;
use App\Policies\Concerns\HandlesUmkmOwnership;
use Illuminate\Auth\Access\Response;

class RekeningBankPolicy
{
    use HandlesUmkmOwnership;

    public function delete(User $user, RekeningBank $rekening): Response
    {
        return $this->ownsResponse($user, $rekening->umkm_id);
    }
}
