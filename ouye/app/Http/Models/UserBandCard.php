<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserBandCard extends Model
{
    use SoftDeletes;

    protected $table = 'cs_user_band_card';
    protected $dateFormat = 'U';

    protected $guarded = [];
}
