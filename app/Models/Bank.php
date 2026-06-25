<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bank extends Model
{
    protected $table = 'bank';
    protected $guarded = [];

    public function rekening(): HasMany { return $this->hasMany(RekeningBank::class, 'bank_id'); }
}
