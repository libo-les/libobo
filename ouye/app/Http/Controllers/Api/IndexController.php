<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
// 阿里短信
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

use App\Http\Models\User;
use App\Http\Models\Api\UserSpokesman;
use App\Http\Models\PlatformAdv;
use App\Http\Models\Config;
use App\Http\Models\PlatformAdvUrl;
use Illuminate\Support\Facades\Storage;

/**
 * 首页
 */
class IndexController extends Controller
{
    /**
     * 首页信息
     */
    public function index(Request $request)
    {
        $uid = $request->token_info['uid'];
        $uaid = $request->token_info['uaid'];
        if (!empty($request->suid)) {
            UserSpokesman::bindSuperior($uid, $uaid, $request->suid);
            $suid = $request->suid;
        } else {
            $suid = UserSpokesman::where('uid', $uid)->orWhere('uaid', $uaid)->value('suid');
        }
        $img_url = config('app.img_url');
        if ($suid) {
            $source_info = User::select('nickname', 'avatar')->find($suid);
        } else {
            $store_info['store_head'] = $img_url . '/' . Config::where('key', 'store_head')->value('value');
            $store_info['store_name'] = Config::where('key', 'store_name')->value('value');
        }
        
        $adv = PlatformAdv::where('ap_id', 1)
            ->where('is_use', 1)
            ->select('id', 'adv_title', 'adv_image', 'url_id', 'url_param')
            ->orderBy('sort', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->each(function ($item, $key) use ($img_url)
            {
                if ($item->url_id) {
                    $item->url = PlatformAdvUrl::where('id', $item->url_id)->value('url');
                }
                $item->adv_image = $img_url . '/' . $item->adv_image;
            });

        return apiReturn(0, 'ok', compact('source_info', 'adv', 'store_info'));
    }
    /**
     * 商家信息
     */
    public function shopInfo()
    {
        $store_name = Config::where('key', 'store_name')->value('value');
        $store_head = config('app.img_url') . '/' . Config::where('key', 'store_head')->value('value');

        return apiReturn(0, 'ok', compact('store_name', 'store_head'));
    }
    
    /**
     * 广告详情
     */
    public function advDetails(Request $request)
    {
        $ad_id = $request->ad_id;

        $ad_info = PlatformAdv::where('id', $ad_id)->first();
        
        $img_url = config('app.img_url');
        $ad_info->adv_code = str_replace('src="', 'src="' . $img_url, htmlspecialchars_decode($ad_info->adv_code));

        return apiReturn(0, 'ok', compact('ad_info'));
    }

    /**
     * 上传文件
     */
    public function uploadFile(Request $request)
    {
        $is_thumb = $request->input('is_thumb', 0);
        // 上传 1特殊任务
        $img_type = $request->input('img_type');
        if (!$img_type) {
            return apiReturn(20000);
        }
        if (!$request->hasFile('image') || !$request->file('image')->isValid()) {
            return apiReturn(1, '上传失败');
        }

        $path = 'storage/' . Storage::putFile('tasks/' . date('ymd'), $request->file('image'));

        return apiReturn(0, 'ok', compact('path'));
    }
}
