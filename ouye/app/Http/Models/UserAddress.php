<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAddress extends Model
{
    use SoftDeletes;
    
    protected $table = 'cs_user_address';
    protected $dateFormat = 'U';

    protected  $fillable = ['uid', 'consignee', 'mobile', 'region', 'province', 'city', 'district', 'detailed_address', 'is_default'];
}
