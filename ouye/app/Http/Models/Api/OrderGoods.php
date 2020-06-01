<?php

namespace App\Http\Models\Api;

use App\Exceptions\ApiException;
use App\Http\Models\Api\Order;
use App\Http\Models\Api\UserStockOrder;
use App\Http\Models\OrderAction;
use App\Http\Models\UserAppend;
use App\Http\Models\UserStockRecord;
use EasyWeChat\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Api
 * 订单商品
 */
class OrderGoods extends \App\Http\Models\OrderGoods
{
    public function order()
    {
        return $this->hasOne('App\Http\Models\Api\Order', 'id', 'order_id');
    }
    /**
     * 商品券支付现金
     */
    public function payCash($request, $order_data)
    {
        // 读取用户openid
        $openid = UserAppend::where('id', $request->token_info['uaid'])->value('openid');

        if (empty($openid)) {
            DB::rollback();
            throw new ApiException('暂时无法支付', 90400);
        }

        $attributes = [
            'trade_type'   => 'JSAPI',
            'body'         => '欧里酒' . date('YmdHis'),
            'detail'       => '欧里酒' . date('YmdHis'),
            'out_trade_no' => $order_data['out_trade_no'],
            'total_fee'    => $order_data['total_cash'] * 100,
            "sub_openid"   => $openid,
        ];
        $mch_config = config('wx_config.service_provider.1520797141');

        $options = [
            // 必要配置
            'app_id' => $mch_config['app_id'],
            'mch_id' => $mch_config['mch_id'],
            'key'    => $mch_config['key'], // API 密钥
        ];
        switch ($order_data['order_pay_type']) {
            case 1:
                $options['notify_url'] = url("api/PayBackGoods/goodsPayHandle");
                break;
            case 2:
                $options['notify_url'] = url("api/PayBackGoods/supplementManPayHandle");
                
                break;
            case 3:
                $options['notify_url'] = url("api/PayBackGoods/supplementAgentPayHandle");
                break;
            case 4:
                // 提货到线下
                $options['notify_url'] = url("api/PayBackGoods/pickPayHandle");
                break;

            default:
                return; 
                break;
        }
        // var_dump($options, config('wx_config.mch_id'), config('wx_config.appid'));die;
        $app = Factory::payment($options);
        $app->setSubMerchant(config('wx_config.mch_id'), config('wx_config.appid'));

        $response = $app->order->unify($attributes);
        if ($response['return_code'] == 'SUCCESS' && $response['result_code'] == 'SUCCESS') {
            $prepayId = $response['prepay_id'];
        } else {
            DB::rollback();
            throw new ApiException('暂时无法支付', 90401);
        }
        $result = $app->jssdk->sdkConfig($prepayId); // 返回数组

        return apiReturn(0, 'ok', [
            'order_id'     => $order_data['order_id'],
            'out_trade_no' => $order_data['out_trade_no'],
            'timeStamp'    => $result['timestamp'],
            'nonceStr'     => $result['nonceStr'],
            'package'      => $result['package'],
            'signType'     => $result['signType'],
            'paySign'      => $result['paySign'],
        ]);
    }
    /**
     * 商品券支付回调处理
     */
    public static function goodsPayDeal($message, $fail)
    {
        if (empty($message['out_trade_no'])) {
            return false;
        }

        $order_info = Order::where('out_trade_no', $message['out_trade_no'])->first();

        if (empty($order_info)) {
            return false;
        }
        if ($order_info['order_status'] != 0 && $order_info['order_status'] != 1) {
            return true;
        }
        if ($message['return_code'] === 'SUCCESS') {
            // return_code 表示通信状态，不代表支付状态
            // 用户是否支付成功
            if ($message['result_code'] === 'SUCCESS') {
                DB::beginTransaction();
                try {
                    $order_info->order_status = 3;
                    $order_info->pay_status   = 2;
                    $order_info->pay_time     = time();
                    $order_info->save();

                    static::where('order_id', $order_info['id'])->update([
                        'order_status' => 3,
                    ]);

                    $order_action_obj               = new OrderAction;
                    $order_action_obj->order_id     = $order_info['id'];
                    $order_action_obj->action_user  = 1;
                    $order_action_obj->action_uid   = $order_info['buyer_id'];
                    $order_action_obj->order_status = 3;
                    $order_action_obj->status_desc  = '已付款';
                    $order_action_obj->save();

                    if ($order_info->stock_type > 1) {
                        UserStockOrder::where('order_id', $order_info['id'])->update([
                            'order_status' => 3,
                            'pay_time'     => time(),
                        ]);
                    }

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
                static::where('order_id', $order_info['id'])->update([
                    'order_status' => 4,
                ]);
                $order_action_obj               = new OrderAction;
                $order_action_obj->order_id     = $order_info['id'];
                $order_action_obj->action_user  = 1;
                $order_action_obj->action_uid   = $order_info['buyer_id'];
                $order_action_obj->order_status = 4;
                $order_action_obj->status_desc  = '支付失败';

                $order_action_obj->save();

                Log::emergency($message['out_trade_no'] . '支付回调失败【3】：' . var_export($message, true));
            }
        } else {
            return $fail('通信失败，请稍后再通知我');
        }
        return true; // 返回处理完成
    }
}
