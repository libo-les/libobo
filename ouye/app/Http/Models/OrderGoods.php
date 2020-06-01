<?php

namespace App\Http\Models;

use Illuminate\Support\Facades\DB;
use App\Exceptions\ApiException;
use EasyWeChat\Factory;

use Illuminate\Database\Eloquent\Model;
use App\Http\Models\UserAppend;

class OrderGoods extends Model
{
    protected $table = 'cs_order_goods';
    protected $dateFormat = 'U';
    
    protected $guarded = [];
}
