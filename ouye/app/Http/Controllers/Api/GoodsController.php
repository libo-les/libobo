<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Models\Goods;
use App\Http\Models\User;
use App\Http\Models\Api\UserSpokesman;
use App\Http\Models\AlbumPicture;
use App\Http\Models\UserStock;
use App\Http\Models\GoodsCategory;
use App\Http\Models\Config;
use App\Http\Models\UserCart;
use Illuminate\Support\Facades\DB;
use EasyWeChat\Factory;
use Illuminate\Support\Facades\Storage;

class GoodsController extends Controller
{
    /**
     * 商品列表
     */
    public function index(Request $request)
    {
        $uid = $request->token_info['uid'];
        $uaid = $request->token_info['uaid'];

        $suid = UserSpokesman::where('uid', $uid)->orWhere('uaid', $uaid)->value('suid');

        $goods_list = Goods::where([
            ['stock', '>', 0],
            ['state', '=', 1]
        ])
            ->orderBy('is_recommend', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20);

        foreach ($goods_list as $key => $value) {
            $goods_list[$key]->picture_path = config('app.img_url') . '/' . AlbumPicture::where('id', $value->picture)->value('pic_cover_small');
            if ($suid) {
                $stock_num = UserStock::where('uid', $suid)->where('goods_id', $value->id)->value('stock_num');
                $goods_list[$key]->stock += $stock_num;
            }
        }
        
        return apiReturn(0, 'ok', compact('goods_list'));
    }
    /**
     * 商品详情
     */
    public function goodsDetail(Request $request)
    {
        // 分享绑定
        if (!empty($request->suid) || !empty($request->scode)) {
            if ($request->scode) {
                $scode = decrypt($request->scode);
                $suid = $scode['uid'];
            } else {
                $suid = $request->suid;
            }
            UserSpokesman::bindSuperior($request->token_info['uid'], $request->token_info['uaid'], $suid);
        }
        $uid = $request->token_info['uid'];
        $uaid = $request->token_info['uaid'];
        $suid = UserSpokesman::where('uid', $uid)->orWhere('uaid', $uaid)->value('suid');

        $goods_id = $request->goods_id;
        $goods_info = Goods::where('id', $goods_id)->first();

        $goods_info->goodsBrand;
        $goods_info->address;

        $goods_info->category_name = GoodsCategory::where('id', $goods_info->category_id)->value('category_name');

        $img_id_array = explode(',', $goods_info['img_id_array']);
        $img_url = config('app.img_url');

        $picture[] = $img_url . '/' . AlbumPicture::where('id', $goods_info['picture'])->value('pic_cover_big');

        AlbumPicture::whereIn('id', $img_id_array)->pluck('pic_cover_big')
            ->each(function ($item, $key) use (&$picture) {
                $picture[] = config('app.img_url') . '/' . $item;
            });
        $goods_info['picture'] = $picture;

        $goods_info['description'] = str_replace('src="', 'src="' . $img_url, htmlspecialchars_decode($goods_info['description']));

        if ($suid) {
            $stock_num = UserStock::where('uid', $suid)->where('goods_id', $goods_id)->value('stock_num');
            $goods_info->stock += $stock_num;
        }
        // 每箱几个
        $each_box = Config::where('key', 'each_box')->value('value');
        $agent_least = Config::where('key', 'agent_least')->value('value');

        return apiReturn(0, 'ok', compact('goods_info', 'each_box', 'agent_least'));
    }

    /**
     * 用户海报生成
     */
    public function createPoster(Request $request)
    {
        if (!$goods_id = $request->goods_id) {
            return apiReturn(20000);
        }
        $uid = $request->token_info['uid'];
        $mch_config = config('wx_config.service_provider.1520797141');
        $options = [
            // 必要配置
            'app_id' => config('wx_config.appid'),
            'secret' => config('wx_config.secret'),
        ];

        $app = Factory::officialAccount($options);
        $access_token = $app->access_token->getToken(config('wx_config.access_token_debug'));

        $user_info = User::where('id', $uid)->first();
        // 普通二维码, 接口B
        $page_path = "pages/notAgent/commonPage/shopData/index";
        $response = http_request('POST', 'https://api.weixin.qq.com/wxa/getwxacodeunlimit', [
            'query' => [
                'access_token' => $access_token['access_token']
            ],
            'json' => [
                'page' => $page_path,
                'scene' => "id_{$goods_id}-u_{$uid}"
            ]
        ]);
        $temp = 'temp/' . date('ymd') . '/' . md5(time() . $uid) . '.jpg';
        Storage::put('./' . $temp, $response);
        $qrcode = config('app.img_url') . '/storage/' . $temp;

        return apiReturn(0, 'ok', compact('qrcode'));
    }


    /**
     * 加入购物车
     */
    public function addCart(Request $request)
    {
        $rules = [
            'goods_id' => 'required|numeric',
            'box_num' => 'required|numeric',
        ];
        $msg = [
            'goods_id.*' => '请选择商品',
            'box_num.*' => '请填写数量',
        ];
        $this->validate($request, $rules, $msg);

        $uid = $request->token_info['uid'];
        $goods_id = $request->goods_id;
        $box_num = $request->box_num;

        $map = [
            'buyer_id' => $uid,
            'goods_id' => $goods_id,
        ];
        $cart_info = UserCart::where($map)->first();

        $goods_info = Goods::where('id', $goods_id)->first();
        if ($cart_info) {
            $cart_info->increment('box_num', $box_num);
            $cart_id = $cart_info->id;
        } else {
            $cart_data = [
                'buyer_id' => $uid,
                'goods_id' => $goods_id,
                'goods_name' => $goods_info->goods_name,
                'price' => $goods_info->promotion_price,
                'box_num' => $box_num,
                'goods_picture' => $goods_info->picture,
            ];
            $cart_info = UserCart::create($cart_data);
            $cart_id = $cart_info->id;
        }

        return apiReturn(0, 'ok', compact('cart_id'));
    }
    /**
     * 删除购物车
     */
    public function removeCart(Request $request)
    {
        if (!$cart_ids = $request->cart_ids) {
            return apiReturn(20001);
        }
        $uid = $request->token_info['uid'];
        $cart_ids = json_decode($cart_ids);

        $result = UserCart::where([
            ['buyer_id', $uid]
        ])
            ->whereIn('id', $cart_ids)
            ->delete();

        return $result ? apiReturn() : apiReturn(1);
    }
    /**
     * 增减设置购物车数量
     */
    public function setCartNum(Request $request)
    {
        $rules = [
            'cart_id' => 'required|numeric',
            'box_num' => 'required|numeric',
        ];
        $msg = [
            'cart_id.*' => '请选择商品',
            'box_num.*' => '请填写数量',
        ];
        $this->validate($request, $rules, $msg);

        $uid = $request->token_info['uid'];
        $cart_id = $request->cart_id;

        $result = UserCart::where([
            'id' => $cart_id,
            'buyer_id' => $uid,
        ])
            ->update([
                'box_num' => $request->box_num,
            ]);

        return $result ? apiReturn() : apiReturn(1);
    }
    /**
     * 购物车列表
     */
    public function cartList(Request $request)
    {
        $uid = $request->token_info['uid'];

        UserCart::join('cs_goods as g', 'cs_user_cart.goods_id', 'g.id')
            ->where('cs_user_cart.buyer_id', $uid)
            ->where(function ($query) {
                $query->where('g.stock', '<=', 0)
                    ->orWhere('g.state', '!=', 1);
            })
            ->delete();

        $lists = UserCart::join('cs_goods as g', 'cs_user_cart.goods_id', 'g.id')
            ->select('cs_user_cart.*', 'g.stock', 'g.state', 'g.goods_name', 'g.promotion_price','g.picture')
            ->where('cs_user_cart.buyer_id', $uid)
            ->paginate(20);
        foreach ($lists as $key => $item) {
            $lists[$key]->picture_path = config('app.img_url') . '/' . AlbumPicture::where('id', $item->picture)->value('pic_cover_small');
        }

        return apiReturn(0, 'ok', compact('lists'));
    }
}
