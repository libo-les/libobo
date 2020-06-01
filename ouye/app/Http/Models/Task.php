<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    protected $table = 'cs_task';
    protected $dateFormat = 'U';
    
    protected $guarded = [];

    protected $casts = [
        'role_type' => 'array',
    ];
}
