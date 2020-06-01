<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class UserStock extends Model
{
    protected $table = 'cs_user_stock';
    protected $dateFormat = 'U';
    protected $guarded = [];

    /**
     * 添加提货订单
     */
    public function addOrder($request, $order_info, $dis_goods)
    {
        $buyer_id = $order_info['buyer_id'];
        $addr_id = $request->addr_id;
        $address_info = UserAddress::where('id', $addr_id)->first();
        $buyer_message = $request->buyer_message ?? '';

        $stock_type = User::where('id', $buyer_id)->value('user_type');

        $out_trade_no = 'g' . date('ymdHis') . mt_rand(10, 99);

        $order_data = [
            'out_trade_no' => $out_trade_no,
            'order_type' => 2,
            'stock_type' => $stock_type,
            'payment_type' => 1,
            'delivery_type' => 2,
            'buyer_id' => $buyer_id,
            'buyer_message' => $buyer_message,
            'receiver_mobile' => $address_info['mobile'],
            'receiver_province' => $address_info['province'],
            'receiver_city' => $address_info['city'],
            'receiver_district' => $address_info['district'],
            'receiver_address' => $address_info['detailed_address'],
            'receiver_name' => $address_info['consignee'],
            'goods_money' => $order_info['goods_money'],
            'order_money' => $order_info['order_money'],
            'pay_money' => $order_info['pay_money'],
            'delivery_money' => $order_info['deliver_fee'],
            'order_status' => 1,
        ];

        $order_res = Order::create($order_data);

        $order_id = $order_res->id;
        $user_stock_order = [
            'out_trade_no' => $out_trade_no,
            'ag_id' => 0,
            'buyer_id' => $buyer_id,
            'order_status' => 1,
            'deal_type' => 2,
            'shipment_type' => 3,
            'order_id' => $order_id,
            'buy_source' => $stock_type,
            'payment_type' => 1,
            'buyer_message' => $buyer_message,
            'goods_money' => $order_info['goods_money'],
            'pay_money' => $order_info['pay_money'],
            'num' => $order_info['total_num'],
        ];
        $stock_order = UserStockOrder::create($user_stock_order);

        foreach ($dis_goods as $key => $dis_good) {
            $goods_buy_num = $dis_good['buy_good']['goods_num'];
            $goods_money_total = $dis_good['goods_info']['promotion_price'] * $goods_buy_num;

            $user_stock_info = UserStock::where('uid', $buyer_id)->where('goods_id', $dis_good['goods_info']['id'])->first();

            $user_stock_record = [
                'uid' => $buyer_id,
                'stock_id' => $user_stock_info['id'],
                'goods_id' => $dis_good['goods_info']['id'],
                'status' => 1,
                'record_source' => 2,
                'stock_num' => $user_stock_info['stock_num'] - $goods_buy_num,
                'deal_type' => 2,
                'deal_num' => -$goods_buy_num,
                'goods_name' => $dis_good['goods_info']['goods_name'],
                'goods_money' => $goods_money_total,
                'pay_money' => 0,
                'shipment_type' => 3,
                'order_id' => $order_id,
                'stock_order_id' => $stock_order['id'],
            ];
            UserStockRecord::create($user_stock_record);
            UserStock::where([
                'uid' => $buyer_id,
                'goods_id' => $dis_good['goods_info']['id'],
            ])->decrement('stock_num', $goods_buy_num);

            $order_goods = [
                'buyer_id' => $buyer_id,
                'order_id' => $order_id,
                'goods_id' => $dis_good['goods_info']['id'],
                'goods_name' => $dis_good['goods_info']['goods_name'],
                'price' => $dis_good['goods_info']['price'],
                'promotion_price' => $dis_good['goods_info']['promotion_price'],
                'cost_price' => $dis_good['goods_info']['cost_price'],
                'num' => $dis_good['buy_good']['goods_num'],
                'pay_money' => 0,
                'goods_picture' => $dis_good['goods_info']['picture'],
                'order_status' => 1,
                'stock_type' => $stock_type,
                'stock_id' => $user_stock_info['id'],
                'stock_buy_num' => $goods_buy_num,
            ];
            $goods_result = OrderGoods::create($order_goods);
        }
        if ($stock_type == 3) {
            Agent::where('uid', $buyer_id)->decrement('stock_num', $order_info['total_num']);
        }

        // 动作
        $order_action_obj = new OrderAction;
        $order_action_obj->order_id = $order_id;
        $order_action_obj->action_user = 1;
        $order_action_obj->action_uid = $order_info['buyer_id'];
        $order_action_obj->order_status = 1;
        $order_action_obj->status_desc = '创建订单';
        $order_action_obj->action_note = $buyer_message;

        $order_action_obj->save();

        return [
            'order_result' => [
                'total_cash' => $order_info['pay_money'],
                'out_trade_no' => $out_trade_no,
                'order_id' => $order_id,
                'order_pay_type' => 1,
            ],
        ];
    }
}
