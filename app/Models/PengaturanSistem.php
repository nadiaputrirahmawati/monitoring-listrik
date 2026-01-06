<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanSistem extends Model
{
    protected $table = 'pengaturan_sistems';

    protected $fillable = [
        'mode',
        'batas_kwh',
        'tarif_per_kwh',
        'status_perangkat',
    ];
}
