<?php

namespace App\Http\Middleware;

use Closure;

class UserAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * 
     * @param  \Closure  $uid
     * @param  \Closure  $uaid
     * @param  \Closure  $api_vs
     * @param  \Closure  $c_at
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $encryptToken = $request->header('token');
        if ( empty($encryptToken) ) {
            return apiReturn(10000, '重新授权', 1);
        }
        $decryptedData = mx_ssl_private_decrypt($encryptToken, config('wx_config.access_token.private_key'));
        if ( empty($decryptedData) ) {
            return apiReturn(10000, '重新授权', 2);
        }
        $token_info = json_decode($decryptedData, true);

        if ($token_info['api_vs'] != config('wx_config.api_vs')) {
            return apiReturn(10001, '秘钥升级');
        }
        if (empty($token_info['uaid'])) {
            return apiReturn(10002, '用户信息错误');
        }
        $request->merge(['token_info' => $token_info]);

        return $next($request);
    }
}
