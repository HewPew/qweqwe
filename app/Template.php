<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    public $fillable = [
        'name', 'user_id', 'data'
    ];
}
