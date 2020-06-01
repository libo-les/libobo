<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $table = 'cs_agent';
    protected $dateFormat = 'U';

    protected $guarded = [];

    public function user()
    {
        return $this->hasOne('App\Http\Models\User', 'id', 'uid');
    }
}

