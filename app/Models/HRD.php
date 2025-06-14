<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HRD extends Model
{
    /** @use HasFactory<\Database\Factories\HRDFactory> */
    use HasFactory;

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
