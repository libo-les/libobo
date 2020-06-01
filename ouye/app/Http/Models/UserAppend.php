<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class UserAppend extends Model
{
    protected $table = 'cs_user_append';
    protected $dateFormat = 'U';

    protected $guarded = [];
}
