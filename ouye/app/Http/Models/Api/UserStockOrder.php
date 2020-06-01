<?php

namespace App\Http\Models\Api;

use Illuminate\Database\Eloquent\Model;
use App\Http\Models\UserStockRecord;
use App\Http\Models\UserStock;
use App\Http\Models\Goods;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Models\Api\Agent;
use App\Http\Models\Api\AgentGradeCondition;
use App\Http\Models\User;
use App\Http\Models\UserCart;
use App\Http\Models\UserAwardRecord;
use App\Http\Models\UserWalletBill;
use App\Http\Models\Api\Order;

class UserStockOrder extends \App\Http\Models\UserStockOrder
{
    /**
     * 代言人添加订单
     */
    public function addOrderMan($request, $order_info, $dis_goods, $agent_info)
    {
        $uid = $order_info['uid'];

        $out_trade_no = 'st' . date('ymdHis') . mt_rand(10, 99);

        $buyer_message = $request->buyer_message ?? '';

        $this->out_trade_no = $out_trade_no;
        $this->buyer_id = $uid;
        $this->order_status = 1;
        $this->deal_type = 1;
        $this->shipment_type = 1;
        $this->buy_user_type = 2;
        $this->buy_source = 1;
        $this->payment_type = 1;
        $this->goods_money = $order_info['sum_money'];
        $this->pay_money = $order_info['pay_money'];
        $this->num = $order_info['num'];
        $this->buyer_message = $buyer_message;
        if ($agent_info) {
            $this->seller_uid = $agent_info->uid;
            $this->ag_id = $agent_info->id;
            $this->buy_source = 3;

            $sum_goods_buy_user_num = 0;
            $sum_goods_money_stock = 0;
            $sum_pay_money_stock = 0;
        }

        $order_res = $this->save();
        $stock_order_id = $this->id;

        $user_stock_obj = new UserStock;
        foreach ($dis_goods as $key => $dis_good) {
            $goods_buy_num = $dis_good['buy_good']['goods_num'];
            $buy_source = 1;
            $buy_stock_type = 1;
            $goods_buy_user_num = 0;

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
                $buy_source = 3;
                $buy_stock_type = 3;
                $goods_money_stock = $dis_good['goods_info']['promotion_price'] * $goods_buy_user_num;
                $pay_money_stock = $dis_good['buy_good']['single_cash'] * $goods_buy_user_num;
                $sum_goods_buy_user_num += $goods_buy_user_num;
                $sum_goods_money_stock += $goods_money_stock;
                $sum_pay_money_stock += $pay_money_stock;

                $user_stock_record = [
                    'uid' => $agent_info['uid'],
                    'stock_id' => $user_stock_info['id'],
                    'goods_id' => $dis_good['goods_info']['id'],
                    'status' => 3,
                    'record_source' => 2,
                    'buy_source' => 3,
                    'stock_num' => $user_stock_info['stock_num'] - $goods_buy_user_num,
                    'each_box_num' => $order_info['each_box'],
                    'deal_type' => 2,
                    'deal_num' => -$goods_buy_user_num,
                    'goods_name' => $dis_good['goods_info']['goods_name'],
                    'goods_money' => $goods_money_stock,
                    'pay_money' => $pay_money_stock,
                    'shipment_type' => 1,
                    'stock_order_id' => $stock_order_id,
                ];
                UserStockRecord::create($user_stock_record);
                UserStock::where([
                    'uid' => $agent_info['uid'],
                    'goods_id' => $dis_good['goods_info']['id'],
                ])->decrement('stock_num', $goods_buy_user_num);
            }
            if ($goods_buy_num > 0) {
                $buy_stock_type = 2;
                Goods::where('id', $dis_good['goods_info']['id'])
                    ->decrement('stock', $goods_buy_num);
            }

            $user_stock_info = $user_stock_obj->where('uid', $uid)->where('goods_id', $dis_good['goods_info']['id'])->first();
            $stock_id = $user_stock_info['id'];
            if (empty($stock_id)) {
                $stock_info = [
                    'uid' => $uid,
                    'goods_id' => $dis_good['goods_info']['id'],
                    'stock_num' => 0
                ];
                $user_stock_info = $user_stock_obj->create($stock_info);
                $stock_id = $user_stock_info->id;
            }
            $record = [
                'uid' => $uid,
                'stock_id' => $stock_id,
                'goods_id' => $dis_good['goods_info']['id'],
                'status' => 3,
                'record_source' => 1,
                'buy_source' => $buy_source,
                'buy_stock_type' => $buy_stock_type,
                'buy_stock_num' => $goods_buy_user_num,
                'stock_num' => $user_stock_info['stock_num'],
                'deal_type' => 1,
                'deal_num' => $dis_good['buy_good']['goods_num'],
                'goods_name' => $dis_good['goods_info']['goods_name'],
                'goods_money' => $dis_good['buy_good']['goods_money'],
                'pay_money' => $dis_good['buy_good']['cash'],
                'shipment_type' => 1,
                'stock_order_id' => $stock_order_id,
            ];
            $goods_result = UserStockRecord::create($record);
        }
        if ($agent_info) {
            $agent_info->decrement('stock_num', $sum_goods_buy_user_num);
        }
        // 清空购物车
        UserCart::where('buyer_id', $uid)->delete();

        return [
            'order_result' => [
                'total_cash' => $order_info['pay_money'],
                'out_trade_no' => $out_trade_no,
                'order_id' => $stock_order_id,
                'order_pay_type' => 2,
            ],
        ];
    }
    /**
     * 代理商添加订单
     */
    public function addOrderAgent($request, $order_info, $dis_goods)
    {
        $out_trade_no = 'st' . date('ymdHis') . mt_rand(10, 99);
        $uid = $order_info['uid'];
        $buyer_message = $request->buyer_message ?? '';

        $this->out_trade_no = $out_trade_no;
        $this->order_status = 1;
        $this->buyer_id = $uid;
        $this->buy_user_type = 3;
        $this->buy_source = 1;
        $this->payment_type = 1;
        $this->goods_money = $order_info['sum_money'];
        $this->pay_money = $order_info['pay_money'];
        $this->num = $order_info['num'];
        $this->buyer_message = $buyer_message;

        $order_res = $this->save();
        $order_id = $this->id;

        $user_stock_obj = new UserStock;
        foreach ($dis_goods as $key => $dis_good) {
            $user_stock_info = $user_stock_obj->where('uid', $uid)->where('goods_id', $dis_good['goods_info']['id'])->first();
            $stock_id = $user_stock_info['id'];
            if (empty($stock_id)) {
                $stock_info = [
                    'uid' => $uid,
                    'goods_id' => $dis_good['goods_info']['id'],
                    'stock_num' => 0
                ];
                $user_stock_info = $user_stock_obj->create($stock_info);
                $stock_id = $user_stock_info->id;
            }
            $record = [
                'uid' => $uid,
                'stock_id' => $stock_id,
                'goods_id' => $dis_good['goods_info']['id'],
                'status' => 3,
                'record_source' => 1,
                'buy_source' => 1,
                'buy_stock_type' => 1,
                'stock_num' => $user_stock_info['stock_num'],
                'each_box_num' => $order_info['each_box_num'],
                'deal_type' => 1,
                'deal_num' => $dis_good['buy_good']['goods_num'],
                'goods_name' => $dis_good['goods_info']['goods_name'],
                'goods_money' => $dis_good['buy_good']['goods_money'],
                'pay_money' => $dis_good['buy_good']['cash'],
                'shipment_type' => 1,
                'stock_order_id' => $order_id,
            ];
            $goods_result = UserStockRecord::create($record);

            Goods::where('id', $dis_good['goods_info']['id'])->decrement('stock', $dis_good['buy_good']['goods_num']);
        }
        // 清空购物车
        UserCart::where('buyer_id', $uid)->delete();

        return [
            'order_result' => [
                'total_cash' => $order_info['pay_money'],
                'out_trade_no' => $out_trade_no,
                'order_id' => $order_id,
                'order_pay_type' => 3,
            ],
        ];
    }
    /**
     * 代言人补货订单回调处理
     */
    public static function supplementManPayDeal($message, $fail)
    {
        if (empty($message['out_trade_no'])) {
            return false;
        }
        $order_info = static::where('out_trade_no', $message['out_trade_no'])->first();

        if (empty($order_info)) {
            return false;
        }
        if ($order_info['order_status'] != 0 && $order_info['order_status'] != 1) {
            return true;
        }
        if ($message['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
            // 用户是否支付成功
            if ($message['result_code'] === 'SUCCESS') {
                DB::beginTransaction();
                try {
                    $buyer_id = $order_info['buyer_id'];
                    $order_info->order_status = 13;
                    $order_info->pay_time = time();
                    $order_info->finish_time = time();
                    $order_info->save();
                    $total_award = 0;

                    $stock_record = UserStockRecord::where('stock_order_id', $order_info->id)->get()->each(function ($item, $key) use (&$order_info, &$total_award) {
                        $stock_info = UserStock::where('id', $item->stock_id)->first();
                        if ($item->uid == $order_info->buyer_id && $item->deal_type == 1) {
                            $stock_num = $stock_info['stock_num'] + $item->deal_num;
                            $accumulate_num = $stock_info['accumulate_num'] + $item->deal_num;
                            $accumulate_money = $stock_info['accumulate_money'] + $item->pay_money;
                            $item->update([
                                'status' => 1,
                                'stock_num' => $stock_num,
                                'pay_time' => time(),
                                'finish_time' => time(),
                            ]);
                            $stock_info->update([
                                'stock_num' => $stock_num,
                                'accumulate_num' => $accumulate_num,
                                'accumulate_money' => $accumulate_money,
                            ]);
                        } elseif ($item->uid == $order_info->seller_uid && $item->deal_type == 2) {
                            $item->update([
                                'status' => 1,
                                'pay_time' => time(),
                                'finish_time' => time(),
                            ]);
                            $total_award += $item->pay_money;
                        }
                    });
                    if ($order_info->buy_source == 3) {
                        $stock_uid = $order_info->seller_uid;
                        $user_award_record_obj = new UserAwardRecord;
                        $user_award_record_obj->uid = $stock_uid;
                        $user_award_record_obj->source = 5;
                        $user_award_record_obj->source_id = $order_info->id;
                        $user_award_record_obj->source_user_id = $order_info->buyer_id;
                        $user_award_record_obj->is_bill = 1;
                        $user_award_record_obj->award = $total_award;
                        $user_award_record_obj->save();
                        $award_record_id = $user_award_record_obj->id;

                        $stock_user_info = User::where('id', $stock_uid)->first();
                        $user_wallet_bill = [
                            'uid' => $stock_uid,
                            'award_type' => 5,
                            'source' => 1,
                            'source_id' => $award_record_id,
                            'source_user_id' => $order_info->buyer_id,
                            'deal_type' => 1,
                            'money_deal' => $total_award,
                            'money_total' => $stock_user_info['balance'] + $total_award,
                            'remark' => '代言人补货',
                            'award_id' => $award_record_id,
                        ];
                        UserWalletBill::create($user_wallet_bill);

                        $stock_user_info->increment('balance', $total_award);
                        $stock_user_info->increment('total_award', $total_award);
                    }
                    // 累计购买金额
                    User::where('id', $order_info->buyer_id)->increment('total_buy_money', $order_info->pay_money);

                    DB::commit();

                    return true;
                } catch (\Exception $e) {
                    DB::rollback();

                    Log::emergency($message['out_trade_no'] . '支付回调失败【2】：' . var_export($message, true));
                    throw $e;
                    return false;
                }

                // 用户支付失败
            } elseif ($message['result_code'] === 'FAIL') {
                $order_info->order_status = 4;
                $order_info->save();

                Log::emergency($message['out_trade_no'] . '支付回调失败【3】：' . var_export($message, true));
            }
        } else {
            return $fail('通信失败，请稍后再通知我');
        }
        return true; // 返回处理完成

    }

    /**
     * 代理商补货订单回调处理
     */
    public static function supplementAgentPayDeal($message, $fail)
    {
        if (empty($message['out_trade_no'])) {
            return false;
        }
        $order_info = static::where('out_trade_no', $message['out_trade_no'])->first();

        if (empty($order_info)) {
            return false;
        }
        if ($order_info['order_status'] != 0 && $order_info['order_status'] != 1) {
            return true;
        }
        if ($message['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
            // 用户是否支付成功
            if ($message['result_code'] === 'SUCCESS') {
                DB::beginTransaction();
                try {
                    $uid = $order_info['buyer_id'];
                    $order_info->order_status = 13;
                    $order_info->pay_time = time();
                    $order_info->finish_time = time();
                    $order_info->save();

                    $stock_record = UserStockRecord::where('stock_order_id', $order_info->id)->get()->each(function ($item, $key) use (&$total_stock_num, &$total_accumulate_num) {
                        $stock_info = UserStock::where('id', $item->stock_id)->first();

                        $stock_num = $stock_info['stock_num'] + $item->deal_num;
                        $accumulate_num = $stock_info['accumulate_num'] + $item->deal_num;
                        $accumulate_money = $stock_info['accumulate_money'] + $item->pay_money;
                        $item->update([
                            'status' => 1,
                            'stock_num' => $stock_num,
                            'pay_time' => time(),
                            'finish_time' => time(),
                        ]);
                        UserStock::where('id', $item->stock_id)->update([
                            'stock_num' => $stock_num,
                            'accumulate_num' => $accumulate_num,
                            'accumulate_money' => $accumulate_money,
                        ]);
                    });
                    $agent_info = Agent::where('uid', $uid)->first();
                    $agent_info->stock_num += $order_info->num;
                    $agent_info->stock_num_accumulate += $order_info->num;
                    $agent_info->stock_pay_accumulate += $order_info->pay_money;

                    // 代理升级信息
                    $agent_upgrade_info = AgentGradeCondition::upgradeAgentInfo($agent_info, $order_info);

                    if ($agent_upgrade_info) {
                        $agent_info->grade = $agent_upgrade_info->grade;
                    }

                    // 确认成为代理
                    if ($agent_info->agent_status == 3) {
                        $$agent_info = Agent::confirmAgent($agent_info, $order_info);
                    }

                    $agent_info->save();
                    // 累计购买金额
                    User::where('id', $order_info->buyer_id)->increment('total_buy_money', $order_info->pay_money);

                    DB::commit();

                    return true;
                } catch (\Exception $e) {
                    DB::rollback();

                    Log::emergency($message['out_trade_no'] . '支付回调失败【2】：' . var_export($message, true));
                    throw $e;
                    return false;
                }

                // 用户支付失败
            } elseif ($message['result_code'] === 'FAIL') {
                $order_info->order_status = 4;
                $order_info->save();

                Log::emergency($message['out_trade_no'] . '支付回调失败【3】：' . var_export($message, true));
            }
        } else {
            return $fail('通信失败，请稍后再通知我');
        }

        return true; // 返回处理完成
    }

    /**
     * 批量取消库存付款
     */
    public static function batchCancelSupplement()
    {
        $auto_cancel_time = time() - 60 * 60 * 2;
        // 自动取消库存付款
        $order_infos = UserStockOrder::where([
            'order_status' => 1,
            'order_id' => 0,
        ])
            ->where('created_at', '<', $auto_cancel_time)
            ->get()
            ->each(function ($item, $key) {
                static::cancelSupplementDeal($item);
            });
            
        // 自动取消订单
        $auto_cancel_order = Order::where([
            ['order_status', 1],
            ['created_at', '<', $auto_cancel_time]
        ])
            ->get()
            ->each(function ($item, $key) {
                Order::cancelBuyDeal($item);
            });

        // 自动收货订单
        $auto_receipt_time = time() - 86400 * 10;
        $auto_receipt_order = Order::where([
            ['order_status', 5],
            ['consign_time', '<', $auto_receipt_time]
        ])
            ->get()
            ->each(function ($item, $key) {
                Order::confirmReceiptDeal($item);
            });

        // 自动交易成功订单
        $auto_success_time = time() - 86400 * 10;
        $auto_success_order = Order::where([
            ['sign_time', '<', $auto_success_time]
        ])
            ->whereIn('order_status', [6, 7])
            ->get()
            ->each(function ($item, $key) {
                Order::finishOrdre($item);
            });

        return;
    }

    /**
     * 取消库存付款
     */
    public static function cancelSupplementDeal($order_info)
    {
        if (empty($order_info)) {
            return apiReturn(1, '数据错误');
        }

        if ($order_info['order_status'] != 1) {
            return apiReturn(1, '不能取消');
        }

        DB::beginTransaction();

        try {
            $stock_order_id = $order_info->id;
            $order_info->order_status = 2;
            $order_info->save();

            $sum_goods_buy_user_num = 0;

            $user_stock_records = UserStockRecord::where('stock_order_id', $stock_order_id)->get();
            foreach ($user_stock_records as $key => $user_stock_record) {
                $deal_num = abs($user_stock_record->deal_num);
                if ($user_stock_record->deal_type == 2) {
                    UserStock::where([
                        'uid' => $user_stock_record->uid,
                        'id' => $user_stock_record->stock_id,
                        'goods_id' => $user_stock_record->goods_id,
                    ])->increment('stock_num', $deal_num);

                    $sum_goods_buy_user_num += $deal_num;
                } elseif ($user_stock_record->deal_type == 1) {
                    if ($user_stock_record->buy_stock_type == 1 || $user_stock_record->buy_stock_type == 2) {
                        Goods::where('id', $user_stock_record->goods_id)
                            ->increment('stock', $deal_num - $user_stock_record->buy_stock_num);
                    }
                }

                $user_stock_record->status = 2;
                $user_stock_record->record_source = 3;
                $user_stock_record->save();
            }
            if ($order_info->seller_uid) {
                Agent::where('uid', $order_info->seller_uid)->increment('stock_num', $sum_goods_buy_user_num);
            }

            DB::commit();
        } catch (\Exception $e) {
            $code = is_integer($e->getCode()) ? $e->getCode() : 90900;
            throw new ApiException($e->getMessage(), $code);
        }

        return apiReturn(0);
    }
}
