<?php

namespace App\Http\Models\Api;

use App\Http\Models\User;
use App\Http\Models\Api\UserSpokesman;

class UserSpokesman extends \App\Http\Models\UserSpokesman
{
    /**
     * 绑定上级
     */
    public static function bindSuperior($uid, $uaid, $suid)
    {
        if ($uid == $suid) {
            return;
        }
        $user_info = User::where('id', $suid)->where('user_type', '>', 1)->first();

        if (empty($user_info)) {
            return;
        }
        if (empty($uid)) {
            $spokesman_info = UserSpokesman::where('uaid', $uaid)->first();
            if ($spokesman_info) {
                $spokesman_info->suid = $suid;
                $spokesman_info->save();
            } else {
                UserSpokesman::create([
                    'uaid' => $uaid,
                    'suid' => $suid,
                    'spokesman_status' => 1
                ]);
            }
        } else {
            $user_type = User::where('id', $uid)->value('user_type');
            // 普通用户查看代言人是否在审核中
            if ($user_type == 1) {
                $spokesman_status = UserSpokesman::where('uid', $uid)->value('spokesman_status');
                if ($spokesman_status === null) {
                    UserSpokesman::create([
                        'uid' => $uid,
                        'suid' => $suid,
                        'spokesman_status' => 1
                    ]);
                } elseif (in_array($spokesman_status, [0, 1])) {
                    UserSpokesman::where('uid', $uid)->update([
                        'suid' => $suid,
                    ]);
                }
            }
        }
    }
}
