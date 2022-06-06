<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ListKs extends Model
{
    public $fillable = [
        'anketa_id', 'product_id', 'product_name',
        'count_indicator',
        'ks_natur',

        'name_job',
        'types',
        'products',
        'construct_system',
        'lvl',
        'height',
        'count_floors',
        'max_height_floor',
        'count_buildings',
        'active',
        'user_id'
    ];
}
