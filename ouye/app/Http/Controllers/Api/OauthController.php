<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;

use App\Http\Models\Api\UserAppend;
use App\Http\Models\User;

/**
 * 小程序授权
 */
class OauthController extends Controller
{
    /**
     * 获取用户openid
     */
    public function getOpenid(Request $request)
    {
        $data = $request->all();
        if ( empty($js_code = $data['js_code']) ) {
            return apiReturn(20001,'缺少code参数');
        }
        // 小程序授权码 兑换 session_key、openid
        $response = http_request('GET', 'https://api.weixin.qq.com/sns/jscode2session', [
            'query' => [
                'appid'      => config('wx_config.appid'),
                'secret'     => config('wx_config.secret'),
                'js_code'    => $js_code,
                'grant_type' => 'authorization_code'
            ]
        ], 'json');

        if ( !empty($response['errcode']) ) {
            return apiReturn(1, $response['errmsg']);
        }

        // 检测是否在账号表中存在
        $obj_append = new UserAppend;
        if ( $append_info = $obj_append->where('openid', $response['openid'])->first() ) {
            $uid = $append_info['uid'];
            $user_ap_id = $append_info['id'];
            $append_info->session_key = $response['session_key'];
            $append_info->save();
        } else {
            $uid = 0;
            $obj_append->openid = $response['openid'];
            $obj_append->session_key = $response['session_key'];
            $obj_append->save();
            $user_ap_id = $obj_append->id;
        }
        
        if ($uid > 0) {
            $user_info = User::userInfo($uid);
            $is_telephone = $user_info['is_telephone'];
            $user_type = $user_info['user_type'];

        } else {
            $user_type = 1;
            $is_telephone = 0;
        }

        $token = UserAppend::getToken($uid, $user_ap_id);

        $is_wss = config('wx_config.is_wss');

        return apiReturn(0, 'ok', compact('token', 'is_telephone', 'is_wss', 'user_type'));
    }
}
