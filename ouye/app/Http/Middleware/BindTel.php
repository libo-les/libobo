<?php

namespace App\Http\Middleware;

use Closure;
/**
 * 验证绑定手机
 */
class BindTel
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (empty($request->token_info['uid'])) {
            return apiReturn(10003);
        }
        return $next($request);
    }
}
