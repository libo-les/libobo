<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class UserStockOrder extends Model
{
    protected $table = 'cs_user_stock_order';
    protected $dateFormat = 'U';
    protected $guarded = [];
}
