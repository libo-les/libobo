<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use EasyWeChat\Factory;

use App\Http\Models\Api\OrderGoods;
use App\Http\Models\Api\UserStockOrder;

class PayBackGoodsController extends Controller
{
    /**
     * 商品支付回调
     */
    public function goodsPayHandle(Request $request)
    {
        $deal_function = '\App\Http\Models\Api\OrderGoods::goodsPayDeal';
        return $this->Handle($deal_function);
    }
    /**
     * 代言人补货
     */
    public function supplementManPayHandle(Request $request)
    {
        $deal_function = '\App\Http\Models\Api\UserStockOrder::supplementManPayDeal';
        return $this->Handle($deal_function);
    }
    /**
     * 代理商补货
     */
    public function supplementAgentPayHandle(Request $request)
    {
        $deal_function = '\App\Http\Models\Api\UserStockOrder::supplementAgentPayDeal';
        return $this->Handle($deal_function);
    }

    private function Handle($deal_function)
    {
        // var_dump(config('app.debug'));die;
        $xml = file_get_contents("php://input");
        $xmldata = @json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

        if (!empty($_POST['out_trade_no']) && $xmldata == false && config('app.debug') == true) {
            $message['out_trade_no'] = $_POST['out_trade_no'];
            $message['return_code'] = 'SUCCESS';
            $message['result_code'] = 'SUCCESS';
            Log::info($message['out_trade_no'] . '支付回调开始：' . var_export($message, true));

            return apiReturn(0, 'ok', $deal_function($message, '失败'));
        } elseif (empty($_POST['out_trade_no']) && !empty($xmldata)) {
            if (empty($xmldata['out_trade_no'])) {
                return;
            }
            Log::info($xmldata['out_trade_no'] . '支付回调开始：' . var_export($xmldata, true));

            $mch_config = config('wx_config.service_provider.1520797141');

            $options = [
                // 必要配置
                'app_id'             => $mch_config['app_id'],
                'mch_id'             => $mch_config['mch_id'],
                'key'                => $mch_config['key'],   // API 密钥
            ];
            $app = Factory::payment($options);

            $app->setSubMerchant(config('wx_config.mch_id'), config('wx_config.appid'));

            $response = $app->handlePaidNotify(function ($message, $fail) use ($deal_function) {
                return $deal_function($message, $fail);
            });
            $response->send();
            exit;
        }
    }
}
