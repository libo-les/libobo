<?php

namespace App\Http\Models\Api;

class UserAppend extends \App\Http\Models\UserAppend
{
    /**
     * 小程序token
     * appid_vs     SaaS版本号
     * uid         用户id
     * uaid      用户扩展id
     */
    static public function getToken($uid, $user_ap_id)
    {
        $accessTokenData = [
            'uid'     => $uid,
            'uaid'  => $user_ap_id,
            'api_vs'   => config('wx_config.api_vs'),
            'c_at'     => time()
        ];
        return mx_ssl_public_encrypt(json_encode($accessTokenData, JSON_UNESCAPED_UNICODE), config('wx_config.access_token.public_key'));

    }
}
