<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $fillable = [
        'masuk_details',
        'keluar_details'
    ];
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function masuk_detail() {
        return $this->hasOne(AbsensiDetail::class, 'id', 'masuk_details');
    }
    public function keluar_detail() {
        return $this->hasOne(AbsensiDetail::class, 'id', 'keluar_details');
    }
}
