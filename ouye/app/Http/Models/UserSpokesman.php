<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSpokesman extends Model
{
    use SoftDeletes;

    protected $table = 'cs_user_spokesman';
    protected $dateFormat = 'U';

    protected $guarded = [];
}
