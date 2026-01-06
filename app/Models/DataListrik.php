<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataListrik extends Model
{
    protected $table = 'data_listriks';

    protected $fillable = [
        'tegangan',
        'arus',
        'watt',
        'energi_kwh',
        'biaya'
    ];
}
