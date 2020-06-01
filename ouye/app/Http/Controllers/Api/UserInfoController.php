<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use WXBizDataCrypt;

use App\Http\Models\User;
use App\Http\Models\Api\UserAppend;
use Illuminate\Support\Facades\Cache;
use App\Http\Models\Api\UserSpokesman;
use App\Http\Models\AgentApplyRecord;
use App\Http\Models\Api\Agent;

class UserInfoController extends Controller
{
    /**
     * 获得用户信息
     */
    public function getUserInfo(Request $request)
    {
        $user_ap_id = $request->token_info['uaid'];
        $obj_append = new UserAppend;
        $append_info = $obj_append->select('nickname', 'avatar')->where('id', $user_ap_id)->first();
        $append_info->user_type = 1;
        if ($request->token_info['uid'] > 0) {
            $uid = $request->token_info['uid'];
            $user_info = User::userInfo($uid);
            $append_info->user_type = $user_type = $user_info['user_type'];
            if ($user_type == 3) {
                $agent_obj = new Agent();
                $agent_info = $agent_obj->where('uid', $uid)->first();
                if ($agent_info->agent_status == 3) {
                    // 30天超时，确认代理商超时
                    $append_info->user_type = $agent_info->overtimeCheck($uid, $user_type);
                }
                $append_info->grade = $agent_info->grade;
            }
        }

        return apiReturn(0, 'ok', $append_info);
    }

    /**
     * 设置用户信息
     */
    public function setUserInfo(Request $request)
    {
        $result = UserAppend::where('id', $request->token_info['uaid'])->update([
            'nickname' => $request->nickname,
            'avatar' => $request->avatar,
        ]);
        return apiReturn(0, '', $result);
    }

    /**
     * 验证用户是否绑定手机
     * @return array
     */
    public function isUserTel(Request $request)
    {
        $uid = $request->token_info['uid'];
        $is_tel = User::where('id', $uid)->where('telephone', '>', 0)->count();

        if ($is_tel) {
            return apiReturn(0);
        }

        return apiReturn(10003);
    }
    /**
     * 发送验证码
     * @return [type] [description]
     */
    public function smsVerify(Request $request)
    {
        $user_ap_id = $request->token_info['uaid'];
        $telephone = $request->telephone;
        // 1添加银行卡addcard 2绑定手机bindtel
        $sms_type = $request->sms_type;

        $verify = mt_rand(1000,9999);
        $param = [
            'phone' => $telephone,
            'sign' => '代乐乐',
            'template' => 'SMS_133005683',
            'message' => [
                'code' => $verify,
            ],
        ];
        // 发送验证码
        $send_res = send_code($param);
        // $send_res = 'OK';
        if($send_res == 'isp.OUT_OF_SERVICE'){

            return apiReturn(1, '短信余额不足');
        }elseif($send_res =='OK'){
            $user_key = $user_ap_id . '_' . $sms_type . '_' . $telephone . '_' . $verify;
            cache(["$user_key" => $verify], 100);

            return apiReturn(0, 'ok', $verify);
        }

        return apiReturn(1, '验证码发送失败', $send_res);
    }


    /**
     * 短信验证绑定手机
     */
    public function bindTelephone(Request $request)
    {
        $user_ap_id = $request->token_info['uaid'];
        $verify = $request->verify;
        $telephone = $request->telephone;
        if (empty($verify) || empty($telephone)) {
            return apiReturn(20001);
        }

        $result = checkVerify($user_ap_id, $telephone, $verify, 'bindtel');
        if (!$result) {
            return apiReturn(1, '验证码错误');
        }

        //绑定手机修改原先信息
        return $this->bindUserInfo($request, $telephone);
    }

    /**
     * 微信授权登录
     */
    public function wxAccreditLogin(Request $request)
    {
        $appid = config('wx_config.appid');
        $encryptedData = $request->encryptedData;
        $iv = $request->iv;

        $session_key = UserAppend::where('id', $request->token_info['uaid'])->value('session_key');
        $pc = new WXBizDataCrypt($appid, $session_key);
        $errCode = $pc->decryptData($encryptedData, $iv, $data);
        if ($errCode != 0) {
            $contents = ['appid' => $appid, 'session_key' => $session_key, 'encryptedData' => $encryptedData, 'iv' => $iv, 'errCode' => $errCode];

            Storage::put("log/wxLogin/ap{$request->token_info['uaid']}.txt", var_export($contents, true));

            return apiReturn(1, '登录失败');
        }
        $telephone = json_decode($data, true);

        //绑定手机修改原先信息
        return $this->bindUserInfo($request, $telephone['phoneNumber']);
    }
    /**
     * 绑定手机用户信息
     */
    public function bindUserInfo($request, $telephone)
    {
        $uaid = $request->token_info['uaid'];
        $append_info = UserAppend::where('id', $uaid)->first();

        if (empty($append_info->nickname)) {
            return apiReturn(1, '先授权用户信息');
        }
        $user_obj = new User;

        $user_info = $user_obj->where('telephone', $telephone)->first();
        if (empty($user_info)) {
            $user_type = 1;
            $user_obj->nickname = $append_info->nickname;
            $user_obj->gender = $append_info->gender;
            $user_obj->avatar = $append_info->avatar;
            $user_obj->telephone = $telephone;
    
            $res = $user_obj->save();
            $uid = $user_obj->id;
        } else {
            $uid = $user_info->id;
            $user_type = $user_info->user_type;
        }
        $res = UserAppend::where('id', $uaid)->update([
            'uid' => $uid,
        ]);
        if (!$res) {
            return apiReturn(1);
        }
        $token = UserAppend::getToken($uid, $uaid);
        $is_telephone = 1;
        // 修改绑定状态
        $man_info = UserSpokesman::where('uid', 0)->where('uaid', $uaid)->first();
        if (!empty($man_info)) {
            // 普通用户查看代言人是否在审核中
            if ($user_type == 1 && $man_info->suid != $uid) {
                $spokesman_status = UserSpokesman::where('uid', $uid)->value('spokesman_status');
                if ($spokesman_status === null) {
                    UserSpokesman::where('uaid', $uaid)->update([
                        'uid' => $uid,
                        'uaid' => 0,
                    ]);
                } elseif (in_array($spokesman_status, [0, 1])) {
                    UserSpokesman::where('uid', $uid)->update([
                        'suid' => $man_info->suid,
                    ]);
                    UserSpokesman::where('uid', 0)->where('uaid', $uaid)->forceDelete();
                }
            } else {
                UserSpokesman::where('uid', 0)->where('uaid', $uaid)->forceDelete();
            }
        }

        return apiReturn(0, 'ok', compact('token', 'is_telephone', 'user_type'));
    }

}
