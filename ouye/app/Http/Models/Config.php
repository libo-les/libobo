<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $table = 'sys_config';
    protected $dateFormat = 'U';
    
    protected $casts = [
        'jvalue' => 'array',
    ];
}
