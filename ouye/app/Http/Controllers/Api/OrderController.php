<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Models\Api\Order;
use App\Http\Models\Api\OrderGoods;
use App\Http\Models\Api\UserSpokesman;
use App\Http\Models\Api\UserStockOrder;
use App\Http\Models\Config;
use App\Http\Models\Goods;
use App\Http\Models\OrderAction;
use App\Http\Models\User;
use App\Http\Models\UserAddress;
use App\Http\Models\UserAwardRecord;
use App\Http\Models\UserStock;
use App\Http\Models\UserStockRecord;
use App\Http\Models\UserWalletBill;
use App\Http\Requests\Api\VerifyOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Models\Agent;
use App\Http\Models\AgentGradeCondition;
use App\Http\Models\UserCart;
use App\Http\Models\AlbumPicture;
use EasyWeChat\Factory;
use App\Http\Models\OrderRefundAction;
use App\Http\Models\OrderRefund;

/**
 * 订单
 */
class OrderController extends Controller
{
    /**
     * 结账单
     */
    public function statement(Request $request)
    {
        // 默认地址
        $uid = $request->token_info['uid'];
        $address_info = UserAddress::where([
            'uid' => $uid,
            'is_default' => 1,
        ])->first();

        $config_obj = new Config;
        $deliver_fee = $config_obj->where('key', 'deliver_fee')->value('jvalue');
        $each_box = $config_obj->where('key', 'each_box')->value('value');

        $user_info = User::userInfo($uid);
        // 代理升级条件
        if ($user_info['user_type'] == 3) {
            $grade_info = Agent::where('uid', $uid)->select('grade', 'stock_num_accumulate', 'stock_pay_accumulate')->first();

            $agent_upgrade_info = AgentGradeCondition::where('grade', $grade_info->grade)->limit(1)->get();
            for ($i = 1; $i <= 2; $i++) {
                $upgrade = $grade_info->grade + $i;
                $agent_res = AgentGradeCondition::where('is_check', 0)
                    ->where('grade', $upgrade)
                    ->first();
                if (empty($agent_res)) {
                    break;
                }
                $agent_upgrade_info->push($agent_res);
            }
        }

        $cart_list = UserCart::join('cs_goods as g', 'cs_user_cart.goods_id', 'g.id')
            ->join('sys_album_picture as p', 'g.picture', 'p.id')
            ->select('cs_user_cart.*', 'g.stock', 'g.state', 'g.goods_name', 'g.promotion_price', 'p.pic_cover_small')
            ->where('cs_user_cart.buyer_id', $uid)
            ->where('g.stock', '>', 0)
            ->where('g.state', 1)
            ->get()
            ->each(function ($item, $key) {
                $item->pic_cover_small = config('app.img_url') . '/' . $item->pic_cover_small;
            });

        return apiReturn(0, 'ok', compact('user_info', 'address_info', 'deliver_fee', 'grade_info', 'each_box', 'agent_upgrade_info', 'cart_list'));
    }

    /**
     * 添加订单
     */
    public function createOrder(VerifyOrder $request)
    {
        $uid = $request->token_info['uid'];
        $buy_goods = json_decode($request->buy_goods, true);

        $sum_cash = 0;
        $sum_money = 0;

        $uid = $request->token_info['uid'];
        $suid = UserSpokesman::where('uid', $uid)->value('suid');
        $stock_type = 1;

        $goods_obj = new Goods;
        foreach ($buy_goods as $key => $buy_good) {
            $goods_info = $goods_obj->where('id', $buy_good['goods_id'])->first();
            // 判断商品是否下架
            if ($goods_info['state'] != 1) {
                return apiReturn(90001, $goods_info['goods_name'] . '该商品券已经下架，无法支付');
            }
            // 判断商品是否有库存
            $goods_num = $goods_info['goods_num'] = $buy_good['goods_num'];
            if ($suid) {
                $user_stock_info = UserStock::where('uid', $suid)->where('goods_id', $buy_good['goods_id'])->first();
                if ($user_stock_info['stock_num'] > 0) {
                    $goods_info->stock += $user_stock_info['stock_num'];
                    $goods_info->user_stock_info = $user_stock_info;

                    if ($stock_type = 1) {
                        $stock_type = User::where('id', $suid)->value('user_type');
                    }
                }
            }
            if ($goods_info['stock'] - $goods_num < 0) {
                return apiReturn(90001, '该商品券库存不足');
            }
            // 限购
            if ($goods_info['max_buy'] > 0) {
                $already_buy_num = OrderGoods::where([
                    'buyer_id' => $uid,
                    'goods_id' => $buy_good['goods_id'],
                    'order_status' => ['in', '1,3,4,5,6,7,8,10,11,12'],
                ])
                    ->sum('num');
                if ($goods_info['max_buy'] < ($already_buy_num + $goods_num)) {
                    return apiReturn(90005, $goods_info['goods_name'] . '限购' . $goods_info['max_buy'] . '个，您已达到最大购买数量，请您修改购买数量');
                }
            }
            // 实际支付
            $cash = $goods_info['promotion_price'] * $goods_num;
            $sum_cash = bcadd($sum_cash, $cash, 2);
            $sum_money = bcadd($sum_money, $goods_info['promotion_price'] * $goods_num, 2);
            $buy_good['cash'] = $cash;
            $buy_good['single_cash'] = $goods_info['promotion_price'];
            // 配送订单
            $dis_goods[] = [
                'goods_info' => $goods_info,
                'buy_good' => $buy_good,
            ];
        }
        $addr_id = $request->addr_id;
        $deliver_fee = 0;

        $user_address = UserAddress::where([
            'id' => $addr_id,
            'uid' => $uid,
        ])->first();
        if (!$user_address) {
            return apiReturn(90011, '请填写配送信息');
        }
        $deliver_fee_info = Config::where('key', 'deliver_fee')->value('jvalue');

        if ($sum_cash >= $deliver_fee_info['over']['money']) {
            $deliver_fee = $deliver_fee_info['over']['fee'];
        } else {
            $deliver_fee = $deliver_fee_info['basic_fee'];
        }
        if ($deliver_fee != $request->deliver_fee) {
            return apiReturn(90011, '配送信息错误');
        }

        $sum_cash = bcadd($sum_cash, $deliver_fee, 2);
        if ($sum_cash != $request->total_cash) {
            return apiReturn(90015, '支付金额不正确');
        }
        $order_info = [
            'buyer_id' => $uid,
            'suid' => $suid,
            'stock_type' => $stock_type,
            'goods_money' => $sum_money,
            'order_money' => $sum_cash,
            'pay_money' => $sum_cash,
            'deliver_fee' => $deliver_fee,
        ];

        DB::beginTransaction();

        try {
            $order_obj = new Order;
            $order_result = $order_obj->addOrder($request, $order_info, $dis_goods);

            if ($order_result) {
                $order_goods_obj = new OrderGoods;
                $pay_result = $order_goods_obj->payCash($request, $order_result['order_result']);

                DB::commit();
            }
        } catch (\Exception $e) {
            $code = is_integer($e->getCode()) ? $e->getCode() : 90900;
            throw new ApiException($e->getMessage(), $code);
        }

        return $pay_result;
    }
    /**
     * 支付结果
     */
    public function payResult(Request $request)
    {
        if (!$order_id = $request->order_id) {
            return apiReturn(20000);
        }
        $order_info = Order::where('id', $order_id)->first();

        $goods_lists = Goods::where([
            ['stock', '>', 0],
            ['state', '=', 1]
        ])
            ->orderBy('is_recommend', 'desc')
            ->paginate(20);

        $img_url = config('app.img_url');
        foreach ($goods_lists as $key => $goods_list) {
            $goods_lists[$key]->picture_path = $img_url . '/' . AlbumPicture::where('id', $goods_list->picture)->value('pic_cover_small');
        }

        return apiReturn(0, 'ok', compact('order_info', 'goods_lists'));
    }
    /**
     * 付款成功主动查询微信订单状态
     */
    public function getWxOrderStatus(Request $request)
    {
        if (!$order_id = $request->order_id) {
            return apiReturn(20000);
        }

        $order_info = Order::where('id', $order_id)->first();

        $mch_config = config('wx_config.service_provider.1520797141');

        $options = [
            // 必要配置
            'app_id' => $mch_config['app_id'],
            'mch_id' => $mch_config['mch_id'],
            'key' => $mch_config['key'], // API 密钥
        ];

        $app = Factory::payment($options);
        $app->setSubMerchant(config('wx_config.mch_id'), config('wx_config.appid'));

        $message = $app->order->queryByOutTradeNumber($order_info['out_trade_no']);

        if ($message['return_code'] == 'SUCCESS' && $message['result_code'] == 'SUCCESS' && $message['trade_state'] == 'SUCCESS') {

            $response = OrderGoods::goodsPayDeal($message, function ($msg) {
                return false;
            });
        } else {
            $response = false;
        }

        if ($response) {
            return apiReturn(0, 'ok', ['trade_state' => 'SUCCESS']);
        } else {
            return apiReturn(0, 'ok', ['trade_state' => 'NOTPAY']);
        }
    }

    /**
     * 取消付款
     */
    public function cancalPay(Request $request)
    {
        if (!$order_id = $request->order_id) {
            return apiReturn(20000);
        }
        $order_info = Order::where([
            'id' => $order_id,
            'order_status' => 1,
        ])->first();
        if (empty($order_info)) {
            return apiReturn(1, '数据错误');
        }

        if ($order_info['order_status'] != 1) {
            return apiReturn(1, '不能取消');
        }

        $result = Order::cancelBuyDeal($order_info);

        return $result;
    }

    /**
     * 再次付款
     */
    public function againPay(Request $request)
    {
        if (!$order_id = $request->order_id) {
            return apiReturn(20000);
        }
        $order_info = Order::where([
            'id' => $order_id,
            'order_status' => 1,
        ])->first();
        if (empty($order_info)) {
            return apiReturn(1, '数据错误');
        }
        if ($order_info['order_status'] != 1) {
            return apiReturn(1, '不能付款');
        }

        DB::beginTransaction();

        try {
            $out_trade_no = 'g' . date('ymdHis') . mt_rand(10, 99);

            $order_info->out_trade_no = $out_trade_no;
            $order_result = $order_info->save();

            if ($order_result) {
                $order_goods_obj = new OrderGoods;
                $order_data = [
                    'total_cash' => $order_info->pay_money,
                    'out_trade_no' => $out_trade_no,
                    'order_id' => $order_id,
                    'order_pay_type' => 1,
                ];

                $pay_result = $order_goods_obj->payCash($request, $order_data);

                DB::commit();
            }
        } catch (\Exception $e) {
            $code = is_integer($e->getCode()) ? $e->getCode() : 90900;
            throw new ApiException($e->getMessage(), $code);
        }

        return $pay_result;
    }

    /**
     * 订单中心
     */
    public function index(Request $request)
    {
        $uid = $request->token_info['uid'];
        $map = [
            ['o.buyer_id', $request->token_info['uid']],
            ['o.is_deleted', 0],
        ];
        if (!empty($request->order_status)) {
            $map[] = ['o.order_status', $request->order_status];
        }
        if (!empty($request->keyword)) {
            $map[] = ['og.goods_name', 'like', "%{$request->keyword}%"];
        }
        // DB::enableQueryLog();
        $order_list = Order::from('cs_order as o')
            ->select('o.*')
            ->where($map)
            ->join('cs_order_goods as og', 'o.id', '=', 'og.order_id')
            ->orderBy('o.id', 'desc')
            ->groupBy('o.id')
            ->paginate(20);
        // var_dump(DB::getQueryLog());die;
        $img_url = config('app.img_url');
        foreach ($order_list as $key => $order_info) {
            foreach ($order_info->orderGoods as $key => $order_goods) {
                $order_info->orderGoods[$key]->picture_path = $img_url . '/' . AlbumPicture::where('id', $order_goods->goods_picture)->value('pic_cover_small');
            }

            $order_list[$key]['order_goods'] = $order_info->orderGoods;
        }

        return apiReturn(0, 'ok', $order_list);
    }

    /**
     * 订单详情
     */
    public function detail(Request $request)
    {
        if (!$order_id = $request->order_id) {
            return apiReturn(20000);
        }
        $order_info = Order::where('id', $order_id)->first();
        $order_goods = OrderGoods::where([
            'buyer_id' => $request->token_info['uid'],
            'order_id' => $request->order_id,
        ])
            ->get()
            ->each(function ($item, $key) {
                $item->goods_picture_path = config('app.img_url') . '/' . AlbumPicture::where('id', $item->goods_picture)->value('pic_cover_small');
            });
        if ($order_info->refund_status > 0) {
            $refund_info = OrderRefund::where('order_id', $order_id)->first();
        }

        return apiReturn(0, 'ok', compact('order_info', 'order_goods', 'refund_info'));
    }
    /**
     * 确认收货
     */
    public function confirmReceipt(Request $request)
    {
        if (!$order_id = $request->order_id) {
            return apiReturn(20000);
        }
        $uid = $request->token_info['uid'];
        $order_info = Order::where('id', $order_id)->where('buyer_id', $uid)->first();
        $delivery_status_enum = [2, 3];
        if ($order_info['order_status'] != 5 || !in_array($order_info['delivery_status'], $delivery_status_enum)) {
            return apiReturn(1, '不能确认收货');
        }
        Order::confirmReceiptDeal($order_info);

        return apiReturn();
    }
    /**
     * 完成交易
     */
    public function editfinishOrdre(Request $request)
    {
        $order_info = Order::where('id', $request->order_id)
            ->whereIn('order_status', [6, 7])
            ->first();
        if ($order_info) {
            Order::finishOrdre($order_info);
        }
        
        return apiReturn();
    }

    /**
     * 发起退款
     *
     * 操作状态
     * 0 未发起
     * 1 买家申请  发起了退款申请,等待卖家处理
     * 2 等待买家退货  卖家已同意退款申请,等待买家退货
     * 3 等待卖家确认收货  买家已退货,等待卖家确认收货
     * 4 等待卖家确认退款  确认收货
     * 5 退款成功
     * 6 拒绝退款
     */
    public function applyRefund(Request $request)
    {
        if (!$order_id = $request->order_id) {
            return apiReturn(20000);
        }
        $uid = $request->token_info['uid'];

        $order_info = Order::where('id', $order_id)->where('buyer_id', $uid)->first();
        // 订单状态 0待回调,不显示 1创建订单,等待支付 2取消支付 3已付款,等待核销,待接单 4支付失败 5已发货 6已收货 7已评价 8申请退款 9退款中 10退款成功,拒绝接单 11退款失败 12分次核销 13交易成功
        $order_status_enum = [3, 5, 6, 10, 11];
        if (!in_array($order_info['order_status'], $order_status_enum)) {
            return apiReturn(1, '不能退款');
        }

        $order_info->order_status = 8;
        $order_info->refund_status = 1;
        $order_info->save();

        OrderGoods::where('order_id', $order_id)->update([
            'order_status' => 8,
            'refund_status' => 1,
        ]);

        // 动作
        $order_action_obj = new OrderAction;
        $order_action_obj->order_id = $order_id;
        $order_action_obj->action_user = 1;
        $order_action_obj->action_uid = $order_info['buyer_id'];
        $order_action_obj->order_status = 8;
        $order_action_obj->status_desc = '发起退款';
        $order_action_obj->action_note = '发起退款';
        $order_action_obj->save();

        // 退款动作
        $order_refund_action_obj = new OrderRefundAction;
        $order_refund_action_obj->order_id = $order_id;
        $order_refund_action_obj->refund_status = 1;
        $order_refund_action_obj->action = '用户申请退款';
        $order_refund_action_obj->action_way = 1;
        $order_refund_action_obj->action_userid = $order_info->buyer_id;
        $order_refund_action_obj->action_username = '';
        $order_refund_action_obj->buyer_message = $request->buyer_message ?? '';
        $order_refund_action_obj->seller_memo = '';
        $order_refund_action_obj->save();

        return apiReturn();
    }

    /**
     * 退货
     */
    public function goodsRefund(Request $request)
    {
        if (!$order_id = $request->order_id) {
            return apiReturn(20000);
        }
        if (!$delivery_no = $request->delivery_no) {
            return apiReturn(20000);
        }
        $uid = $request->token_info['uid'];

        $order_info = Order::where('id', $order_id)->where('buyer_id', $uid)->first();

        if ($order_info['order_status'] != 9 || $order_info['refund_status'] != 2) {
            return apiReturn(1, '不能退款');
        }

        $order_info->refund_status = 3;
        $order_info->save();

        OrderGoods::where('order_id', $order_id)->update([
            'refund_status' => 3,
        ]);

        // 动作
        $order_action_obj = new OrderAction;
        $order_action_obj->order_id = $order_id;
        $order_action_obj->action_user = 1;
        $order_action_obj->action_uid = $order_info['buyer_id'];
        $order_action_obj->order_status = 9;
        $order_action_obj->status_desc = '买家已退货';
        $order_action_obj->action_note = '买家已退货,等待卖家确认收货';
        $order_action_obj->save();

        $out_trade_no = 'r' . $order_info['out_trade_no'];
        $order_refund_data = [
            'refund_trade_no' => $out_trade_no,
            'order_id' => $order_id,
            'refund_status' => 3,
            'buyer_id' => $order_info['buyer_id'],
            'delivery_no' => $delivery_no,
        ];
        OrderRefund::create($order_refund_data);

        // 退款动作
        $order_refund_action_obj = new OrderRefundAction;
        $order_refund_action_obj->order_id = $order_id;
        $order_refund_action_obj->refund_status = 3;
        $order_refund_action_obj->action = '买家已退货';
        $order_refund_action_obj->action_way = 1;
        $order_refund_action_obj->action_userid = $order_info->buyer_id;
        $order_refund_action_obj->action_username = '';
        $order_refund_action_obj->buyer_message = $request->buyer_message ?? '';
        $order_refund_action_obj->seller_memo = '';
        $order_refund_action_obj->save();

        return apiReturn();
    }

    /**
     * 刪除订单
     */
    public function delete(Request $request)
    {
        if (!$order_id = $request->order_id) {
            return apiReturn(20000);
        }
        $uid = $request->token_info['uid'];
        $order_info = Order::where('id', $order_id)->where('buyer_id', $uid)->first();

        if (empty($order_info)) {
            return apiReturn(1, '数据错误');
        }
        $order_status_enum = [2, 13];
        if (!in_array($order_info->order_status, $order_status_enum)) {
            return apiReturn(1, '不能刪除订单');
        }
        $order_info->is_deleted = 1;
        $order_info->save();

        return apiReturn();
    }
}
