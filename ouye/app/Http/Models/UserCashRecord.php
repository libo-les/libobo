<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserCashRecord extends Model
{
    use SoftDeletes;

    protected $table = 'cs_user_cash_record';
    protected $dateFormat = 'U';

    protected $guarded = [];
}
