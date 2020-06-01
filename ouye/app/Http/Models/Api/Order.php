<?php

namespace App\Http\Models\Api;

use App\Http\Models\Api\OrderGoods;
use App\Http\Models\Goods;
use App\Http\Models\OrderAction;
use App\Http\Models\UserAddress;
use App\Http\Models\UserStock;
use App\Http\Models\UserStockOrder as UserStockOrderModel;
use App\Http\Models\UserStockRecord;
use App\Http\Models\Agent as AgentModel;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ApiException;
use App\Http\Models\Config;
use App\Http\Models\UserAwardRecord;
use App\Http\Models\User;
use App\Http\Models\UserWalletBill;

/**
 * Api
 * 订单
 */
class Order extends \App\Http\Models\Order
{
    public function orderGoods()
    {
        return $this->hasMany('App\Http\Models\Api\OrderGoods', 'order_id', 'id');
    }
    /**
     * 添加订单
     */
    public function addOrder($request, $order_info, $dis_goods)
    {

        $addr_id = $request->addr_id;
        $address_info = UserAddress::where('id', $addr_id)->first();
        $buyer_message = $request->buyer_message ?? '';

        $out_trade_no = 'g' . date('ymdHis') . mt_rand(10, 99);

        $this->out_trade_no = $out_trade_no;
        $this->order_type = 1;
        $this->payment_type = 1;
        $this->delivery_type = 2;
        $this->buyer_id = $order_info['buyer_id'];
        $this->buyer_message = $buyer_message;
        $this->receiver_mobile = $address_info['mobile'];
        $this->receiver_province = $address_info['province'];
        $this->receiver_city = $address_info['city'];
        $this->receiver_district = $address_info['district'];
        $this->receiver_address = $address_info['detailed_address'];
        $this->receiver_name = $address_info['consignee'];
        $this->goods_money = $order_info['goods_money'];
        $this->order_money = $order_info['order_money'];
        $this->pay_money = $order_info['pay_money'];
        $this->delivery_money = $order_info['deliver_fee'];
        $this->order_status = 1;
        if ($order_info['stock_type'] > 1) {
            $this->stock_type = $order_info['stock_type'];
        }

        $order_obj = $this->save();
        $order_id = $this->id;
        if ($order_info['stock_type'] > 1) {
            $user_type = User::where('id', $order_info['buyer_id'])->value('user_type');
            $user_stock_order = [
                'out_trade_no' => $out_trade_no,
                'seller_uid' => $order_info['suid'],
                'ag_id' => 0,
                'buyer_id' => $order_info['buyer_id'],
                'order_status' => 1,
                'deal_type' => 2,
                'shipment_type' => 2,
                'order_id' => $order_id,
                'buy_user_type' => $user_type,
                'buy_source' => $order_info['stock_type'],
                'payment_type' => 1,
                'buyer_message' => $buyer_message,
            ];
            
            $stock_order = UserStockOrderModel::create($user_stock_order);
            $sum_goods_buy_user_num = 0;
            $sum_goods_money_stock = 0;
            $sum_pay_money_stock = 0;
        }

        foreach ($dis_goods as $key => $dis_good) {
            $goods_buy_num = $dis_good['buy_good']['goods_num'];
            $goods_stock_type = 1;

            if ($dis_good['goods_info']['user_stock_info']) {
                $user_stock_info = $dis_good['goods_info']['user_stock_info'];
                $user_stock = $user_stock_info['stock_num'];
                if ($user_stock >= $goods_buy_num) {
                    $goods_buy_user_num = $goods_buy_num;
                    $goods_buy_num = 0;
                } else {
                    $goods_buy_user_num = $user_stock;
                    $goods_buy_num = $goods_buy_num - $user_stock;
                }
                $goods_stock_type = 3;
                $goods_money_stock = $dis_good['goods_info']['promotion_price'] * $goods_buy_user_num;
                $pay_money_stock = $dis_good['buy_good']['single_cash'] * $goods_buy_user_num;
                $sum_goods_buy_user_num += $goods_buy_user_num;
                $sum_goods_money_stock += $goods_money_stock;
                $sum_pay_money_stock += $pay_money_stock;

                $user_stock_record = [
                    'uid' => $order_info['suid'],
                    'stock_id' => $user_stock_info['id'],
                    'goods_id' => $dis_good['goods_info']['id'],
                    'status' => 1,
                    'record_source' => 2,
                    'buy_source' => $order_info['stock_type'],
                    'stock_num' => $user_stock_info['stock_num'] - $goods_buy_user_num,
                    'deal_type' => 2,
                    'deal_num' => -$goods_buy_user_num,
                    'goods_name' => $dis_good['goods_info']['goods_name'],
                    'goods_money' => $goods_money_stock,
                    'pay_money' => $pay_money_stock,
                    'shipment_type' => 2,
                    'order_id' => $order_id,
                    'stock_order_id' => $stock_order['id'],
                ];
                UserStockRecord::create($user_stock_record);
                UserStock::where([
                    'uid' => $order_info['suid'],
                    'goods_id' => $dis_good['goods_info']['id'],
                ])->decrement('stock_num', $goods_buy_user_num);
            }

            if ($goods_buy_num > 0) {
                $goods_stock_type = 2;
                Goods::where([
                    ['id', '=', $dis_good['goods_info']['id']],
                    ['stock', '>', 0],
                ])->decrement('stock', $goods_buy_num);
            }

            $order_goods = [
                'buyer_id' => $order_info['buyer_id'],
                'order_id' => $order_id,
                'goods_id' => $dis_good['goods_info']['id'],
                'goods_name' => $dis_good['goods_info']['goods_name'],
                'price' => $dis_good['goods_info']['price'],
                'promotion_price' => $dis_good['goods_info']['promotion_price'],
                'cost_price' => $dis_good['goods_info']['cost_price'],
                'num' => $dis_good['buy_good']['goods_num'],
                'pay_money' => $dis_good['buy_good']['cash'],
                'goods_picture' => $dis_good['goods_info']['picture'],
                'order_status' => 1,
                'stock_type' => $goods_stock_type,
                'stock_id' => $dis_good['goods_info']['user_stock_info']['id'] ?? 0,
                'stock_buy_num' => $goods_buy_user_num ?? 0,
            ];
            $goods_result = OrderGoods::create($order_goods);
        }
        if ($order_info['stock_type'] > 1) {
            $stock_order->goods_money = $sum_goods_money_stock;
            $stock_order->pay_money = $sum_pay_money_stock;
            $stock_order->num = $sum_goods_buy_user_num;
            $stock_order->save();

            if ($order_info['stock_type'] == 3) {
                AgentModel::where('uid', $order_info['suid'])->decrement('stock_num', $sum_goods_buy_user_num);
            }
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

    /**
     * 取消订单
     */
    public static function cancelBuyDeal($order_info)
    {
        if (empty($order_info)) {
            return apiReturn(1, '数据错误');
        }

        if ($order_info['order_status'] != 1) {
            return apiReturn(1, '不能取消');
        }

        DB::beginTransaction();

        try {
            $order_id = $order_info->id;
            $order_info->order_status = 2;
            $order_info->save();
            if ($order_info['stock_type'] > 1) {
                $user_stock_order_info = UserStockOrderModel::where('order_id', $order_id)->first();
                $user_stock_order_info->order_status = 2;
                $user_stock_order_info->save();

                if ($user_stock_order_info->buy_source == 3) {
                    AgentModel::where([
                        'uid' => $user_stock_order_info->seller_uid,
                    ])->increment('stock_num', $user_stock_order_info->num);
                }
            }

            $order_goods_infos = OrderGoods::where('order_id', $order_id)->get();
            foreach ($order_goods_infos as $key => $order_goods_info) {
                $order_goods_info->order_status = 2;
                $order_goods_info->save();

                $buy_num = $order_goods_info['num'];
                if ($order_info['stock_type'] > 1) {
                    $stock_record = UserStockRecord::where('order_id', $order_id)->where('goods_id', $order_goods_info['goods_id'])->first();

                    $deal_num = abs($stock_record['deal_num']);

                    // 取消订单，记录无效
                    $stock_record->status = 2;
                    $stock_record->record_source = 3;
                    $stock_record->save();

                    UserStock::where([
                        'uid' => $stock_record['uid'],
                        'goods_id' => $stock_record['goods_id'],
                    ])->increment('stock_num', $deal_num);

                    $buy_num -= $deal_num;
                }
                if ($buy_num > 0) {
                    Goods::where('id', $order_goods_info['goods_id'])->increment('stock', $buy_num);
                }
            }

            // 动作
            $order_action_obj = new OrderAction;
            $order_action_obj->order_id = $order_id;
            $order_action_obj->action_user = 1;
            $order_action_obj->action_uid = $order_info['buyer_id'];
            $order_action_obj->order_status = 2;
            $order_action_obj->status_desc = '取消支付';
            $order_action_obj->action_note = '取消支付';
            $order_action_obj->save();

            DB::commit();
        } catch (\Exception $e) {
            $code = is_integer($e->getCode()) ? $e->getCode() : 90900;
            throw new ApiException($e->getMessage(), $code);
        }

        return apiReturn(0);
    }

    /**
     * 确认收货
     */
    public static function confirmReceiptDeal($order_info)
    {
        DB::beginTransaction();

        try {
            $order_id = $order_info->id;
        
            $order_info->order_status = 6;
            $order_info->delivery_status = 4;
            $order_info->sign_time = time();
            $order_info->save();
            // 订单商品
            OrderGoods::where('order_id', $order_id)->update([
                'order_status' => 6,
                'delivery_status' => 4,
            ]);
            if ($order_info->stock_type != 1 && $order_info->order_type == 1) {
                $stock_order_info = UserStockOrderModel::where('order_id', $order_id)->first();
                $stock_order_info->order_status = 6;
                $stock_order_info->save();
    
                UserStockRecord::where('order_id', $order_id)->update([
                    'status' => 1,
                ]);
            } elseif ($order_info->order_type == 2) {
                $stock_order_info = UserStockOrderModel::where('order_id', $order_id)->first();
                $stock_order_info->order_status = 6;
                $stock_order_info->save();
            }
    
            // 动作
            $order_action_obj = new OrderAction;
            $order_action_obj->order_id = $order_id;
            $order_action_obj->action_user = 1;
            $order_action_obj->action_uid = $order_info['buyer_id'];
            $order_action_obj->order_status = 6;
            $order_action_obj->delivery_status = 4;
            $order_action_obj->status_desc = '确认收货';
            $order_action_obj->action_note = '确认收货';
            $order_action_obj->save();

            DB::commit();
        } catch (\Exception $e) {
            $code = is_integer($e->getCode()) ? $e->getCode() : 90900;
            throw new ApiException($e->getMessage(), $code);
        }
    }

    /**
     * 完成交易
     */
    public static function finishOrdre($order_info)
    {
        DB::beginTransaction();

        try {
            $order_id = $order_info->id;
        
            $order_info->order_status = 13;
            $order_info->finish_time = time();
            $order_info->save();
            // 订单商品
            OrderGoods::where('order_id', $order_id)->update([
                'order_status' => 13,
            ]);
            // 累计购买金额
            User::where('id', $order_info->buyer_id)->increment('total_buy_money', $order_info->pay_money - $order_info->delivery_money);

            if ($order_info->stock_type != 1 && $order_info->order_type == 1) {
                $stock_order_info = UserStockOrderModel::where('order_id', $order_id)->first();
                $stock_order_info->order_status = 13;
                $stock_order_info->save();
                $stock_uid = $stock_order_info->seller_uid;
                // 出售商品奖励
                if ($order_info->stock_type == 2) {
                    $sell_award_scale = Config::where('key', 'sell_award_scale')->value('value') * 0.01;
                    if($sell_award_scale <= 0){
                        goto no_award;
                    }
                    $goods_records = UserStockRecord::where('order_id', $order_id)->get();
                    foreach ($goods_records as $key => $goods_record) {
                        $award = bcmul($sell_award_scale, $goods_record->pay_money, 2);
                        if ($award > 0) {
                            $user_award_record = [
                                'uid' => $stock_uid,
                                'order_id' => $order_id,
                                'goods_id' => $goods_record->goods_id,
                                'source' => 6,
                                'source_id' => $stock_order_info['id'],
                                'source_user_id' => $stock_order_info['buyer_id'],
                                'is_bill' => 0,
                                'award' => $award,
                            ];
                            UserAwardRecord::create($user_award_record);
                        }
                    }
                }
                no_award:

                UserStockRecord::where('order_id', $order_id)->update([
                    'status' => 1,
                ]);
                // 出售商品
                $user_award_record = [
                    'uid' => $stock_uid,
                    'order_id' => $order_id,
                    'source' => 2,
                    'source_id' => $stock_order_info['id'],
                    'source_user_id' => $stock_order_info['buyer_id'],
                    'is_bill' => 1,
                    'award' => $stock_order_info['pay_money'],
                ];
                $user_award_record_res = UserAwardRecord::create($user_award_record);
                $award_record_id = $user_award_record_res->id;
    
                $stock_user_info = User::where('id', $stock_uid)->first();
                $user_wallet_bill = [
                    'uid' => $stock_uid,
                    'award_type' => 2,
                    'source' => 1,
                    'source_id' => $award_record_id,
                    'source_user_id' => $stock_order_info['buyer_id'],
                    'deal_type' => 1,
                    'money_deal' => $stock_order_info['pay_money'],
                    'money_total' => $stock_user_info['balance'] + $stock_order_info['pay_money'],
                    'remark' => '用户出售',
                    'award_id' => $award_record_id,
                ];
                UserWalletBill::create($user_wallet_bill);
    
                $stock_user_info->increment('balance', $stock_order_info['pay_money']);
                $stock_user_info->increment('total_award', $stock_order_info['pay_money']);
            } elseif ($order_info->order_type == 2) {
                $stock_order_info = UserStockOrderModel::where('order_id', $order_id)->first();
                $stock_order_info->order_status = 13;
                $stock_order_info->save();
            }
    
            // 动作
            $order_action_obj = new OrderAction;
            $order_action_obj->order_id = $order_id;
            $order_action_obj->action_user = 1;
            $order_action_obj->action_uid = $order_info['buyer_id'];
            $order_action_obj->order_status = 13;
            $order_action_obj->delivery_status = 4;
            $order_action_obj->status_desc = '交易成功';
            $order_action_obj->action_note = '交易成功';
            $order_action_obj->save();

            DB::commit();
        } catch (\Exception $e) {
            $code = is_integer($e->getCode()) ? $e->getCode() : 90900;
            throw new ApiException($e->getMessage(), $code);
        }        
    }
}
