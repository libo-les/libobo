<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'cs_order';
    protected $dateFormat = 'U';
    
    protected $guarded = [];
}
