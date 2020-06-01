<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class OrderRefund extends Model
{
    protected $table = 'cs_order_refund';
    protected $dateFormat = 'U';

    protected $guarded = [];
}
