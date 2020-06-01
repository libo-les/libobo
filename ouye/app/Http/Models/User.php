<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'cs_user';
    protected $dateFormat = 'U';

    public static function userInfo($uid)
    {
        $user_info = static::where('id', $uid)->select('user_type', 'user_type_check', 'telephone')->first();
        $user_type = $user_info['user_type_check'];
        if ($user_type == 0) {
            $user_type = $user_info['user_type'] ?? 1;
        }
        $is_telephone = $user_info['telephone'] > 0 ? 1 : 0;

        return compact('user_type', 'is_telephone');
    }
}
