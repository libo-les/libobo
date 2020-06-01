<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class UserCart extends Model
{
    protected $table = 'cs_user_cart';
    protected $dateFormat = 'U';

    protected $guarded = [];
}
