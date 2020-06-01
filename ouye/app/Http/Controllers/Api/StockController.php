<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Models\Agent;
use App\Http\Models\AlbumPicture;
use App\Http\Models\Api\AgentGradeCondition;
use App\Http\Models\Api\OrderGoods;
use App\Http\Models\Api\UserSpokesman;
use App\Http\Models\Api\UserStockOrder;
use App\Http\Models\Config;
use App\Http\Models\Goods;
use App\Http\Models\Order;
use App\Http\Models\UserAddress;
use App\Http\Models\UserAwardRecord;
use App\Http\Models\UserStock;
use App\Http\Models\UserStockRecord;
use EasyWeChat\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 用户库存
 */
class StockController extends Controller {
	/**
	 * 我的库存
	 */
	public function index(Request $request) {

		$uid = $request->token_info['uid'];
		$stock_infos = UserStock::join('cs_goods as g', 'cs_user_stock.goods_id', 'g.id')
			->select('cs_user_stock.*', 'g.goods_name', 'g.promotion_price', 'g.picture')
			->where('cs_user_stock.uid', $uid)
			->paginate(20);

		$sell_award_scale = Config::where('key', 'sell_award_scale')->value('value') * 0.01;
		foreach ($stock_infos as $key => $stock_info) {
			$stock_infos[$key]->picture_path = config('app.img_url') . '/' . AlbumPicture::where('id', $stock_info->picture)->value('pic_cover_small');
			// 今日返利
			$stock_infos[$key]->award_today = UserAwardRecord::where('uid', $stock_info->goods_id)
				->WhereBetween('created_at', [strtotime('today'), strtotime('tomorrow')])
				->sum('award');
			// 预计收益
			$stock_infos[$key]->award_predict = $stock_info->promotion_price * $stock_info->stock_num * (1 + $sell_award_scale);
		}

		return apiReturn(0, 'ok', $stock_infos);
	}
	/**
	 * 进货列表
	 */
	public function replenishList(Request $request) {
		$goods_infos = Goods::where([
			['stock', '>', 0],
			['state', '=', 1],
		])
			->paginate(20);
		foreach ($goods_infos as $key => $goods_info) {
			$goods_infos[$key]->picture_path = config('app.img_url') . '/' . AlbumPicture::where('id', $goods_info->picture)->value('pic_cover_small');
		}
		$each_box = Config::where('key', 'each_box')->value('value');
		$agent_least = Config::where('key', 'agent_least')->value('value');

		return apiReturn(0, 'ok', compact('goods_infos', 'each_box', 'agent_least'));
	}

	/**
	 * 代言人添加仓库订单
	 */
	public function createOrderMan(Request $request) {
		$uid = $request->token_info['uid'];
		$buy_goods = json_decode($request->buy_goods, true);
		$total_box_num = $request->total_box_num;

		$each_box = Config::where('key', 'each_box')->value('value');
		$total_num = $total_box_num * $each_box;

		$man_info = UserSpokesman::where('uid', $uid)->first();
		$agent_info = null;
		if ($man_info['suid']) {
			$agent_info = Agent::where('uid', $man_info['suid'])->where('agent_status', 5)->first();
		}
		$stock_type = 1;

		$sum_cash = 0;
		$sum_money = 0;
		$num = 0;
		$goods_obj = new Goods;
		// 验证金额
		foreach ($buy_goods as $key => $buy_good) {
			$goods_info = $goods_obj->where('id', $buy_good['goods_id'])->first();
			// 判断商品券是否下架
			if ($goods_info['state'] != 1) {
				return apiReturn(90001, $goods_info['goods_name'] . '该商品券已经下架，无法支付');
			}
			// 判断商品券是否有库存
			$num += $goods_num = $buy_good['goods_num'] = $buy_good['box_num'] * $each_box;
			if ($agent_info) {
				$user_stock_info = UserStock::where('uid', $agent_info->uid)->where('goods_id', $buy_good['goods_id'])->first();
				if ($user_stock_info['stock_num'] > 0) {
					$goods_info->stock += $user_stock_info['stock_num'];
					$goods_info->user_stock_info = $user_stock_info;
				}
			}

			if ($goods_info['stock'] - $goods_num < 0) {
				return apiReturn(90001, '该商品券库存不足');
			}
			// 实际支付
			$cash = $goods_info['promotion_price'] * $goods_num;
			$sum_cash = bcadd($sum_cash, $cash, 2);
			$goods_money = $goods_info['promotion_price'] * $goods_num;
			$sum_money = bcadd($sum_money, $goods_money, 2);
			$buy_good['cash'] = $cash;
			$buy_good['single_cash'] = $goods_info['promotion_price'];
			$buy_good['goods_money'] = $goods_money;
			// 配送订单
			$dis_goods[] = [
				'goods_info' => $goods_info,
				'buy_good' => $buy_good,
			];
		}
		if ($sum_cash != $request->total_cash) {
			return apiReturn(90015, '支付金额不正确');
		}
		if ($total_num != $num) {
			return apiReturn(90016, '购买数量不正确');
		}

		$order_info = [
			'uid' => $uid,
			'sum_money' => $sum_money,
			'pay_money' => $sum_cash,
			'each_box' => $each_box,
			'num' => $num,
		];
		// 生成订单
		DB::beginTransaction();

		try {
			$stock_order_obj = new UserStockOrder;
			$order_result = $stock_order_obj->addOrderMan($request, $order_info, $dis_goods, $agent_info);

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
	 * 代理商添加仓库订单
	 */
	public function createOrderAgent(Request $request) {
		$uid = $request->token_info['uid'];
		$buy_goods = json_decode($request->buy_goods, true);
		$total_box_num = $request->total_box_num;
		$each_box = Config::where('key', 'each_box')->value('value');
		$total_num = $total_box_num * $each_box;
		$pay_money = $request->total_cash;

		$agent_least = Config::where('key', 'agent_least')->value('value');
		if ($total_num < $agent_least) {
			return apiReturn(1, '低于最低购买数量');
		}

		$agent_info = Agent::where('uid', $uid)->first();
		if ($agent_info['agent_status'] != 3 && $agent_info['agent_status'] != 5) {
			return apiReturn(1, '您不是代理商没有资格购买');
		}
		// 代理升级信息
		$new_agent_info = collect();
		$new_agent_info->grade = $agent_info->grade;
		$new_agent_info->stock_num_accumulate = $agent_info->stock_num_accumulate + $total_num;
		$new_agent_info->stock_pay_accumulate = $agent_info->stock_pay_accumulate + $pay_money;
		$new_order_info = collect();
		$new_order_info->num = $total_num;
		$new_order_info->pay_money = $pay_money;

		$agent_upgrade_info = AgentGradeCondition::upgradeAgentInfo($new_agent_info, $new_order_info);
		$agent_grade_info = AgentGradeCondition::where('grade', $agent_info->grade)->first();
		if ($agent_upgrade_info) {
			$agent_grade_info = $agent_upgrade_info;
		}
		if (empty($agent_grade_info)) {
			return apiReturn(1, '商家停止进货');
		}
		$sum_cash = 0;
		$sum_money = 0;
		$num = 0;
		$discount = $agent_grade_info['discount'] * 0.1;
		$goods_obj = new Goods;
		// 验证金额
		foreach ($buy_goods as $key => $buy_good) {
			$goods_info = $goods_obj->where('id', $buy_good['goods_id'])->first();
			// 判断商品券是否下架
			if ($goods_info['state'] != 1) {
				return apiReturn(90001, $goods_info['goods_name'] . '该商品券已经下架，无法支付');
			}
			// 判断商品券是否有库存
			$num += $goods_num = $buy_good['goods_num'] = $buy_good['box_num'] * $each_box;
			if ($goods_info['stock'] - $goods_num < 0) {
				return apiReturn(90001, '该商品券库存不足');
			}
			// 实际支付
			$cash = (int) ($goods_info['promotion_price'] * 100) * $discount * $goods_num;
			$sum_cash = bcadd($sum_cash, ceil($cash) / 100, 2);
			$goods_money = $goods_info['promotion_price'] * $goods_num;
			$sum_money = bcadd($sum_money, $goods_money, 2);
			$buy_good['cash'] = $cash;
			$buy_good['goods_money'] = $goods_money;
			// 配送订单
			$dis_goods[] = [
				'goods_info' => $goods_info,
				'buy_good' => $buy_good,
			];
		}
		if ($sum_cash != $pay_money) {
			return apiReturn(90015, '支付金额不正确');
		}
		if ($total_num != $num) {
			return apiReturn(90016, '购买数量不正确');
		}

		$order_info = [
			'uid' => $uid,
			'sum_money' => $sum_money,
			'pay_money' => $pay_money,
			'num' => $num,
			'each_box_num' => $each_box,
		];
		// 生成订单
		DB::beginTransaction();

		try {
			$stock_order_obj = new UserStockOrder;
			$order_result = $stock_order_obj->addOrderAgent($request, $order_info, $dis_goods, $agent_info);

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
	 * 取消付款
	 */
	public function cancelSupplementPay(Request $request) {
		if (!$stock_order_id = $request->stock_order_id) {
			return apiReturn(20000);
		}
		$order_info = UserStockOrder::where([
			'id' => $stock_order_id,
			'order_status' => 1,
		])->first();
		if (empty($order_info)) {
			return apiReturn(1, '数据错误');
		}

		if ($order_info['order_status'] != 1) {
			return apiReturn(1, '不能取消');
		}

		$result = UserStockOrder::cancelSupplementDeal($order_info);

		return $result;
	}

	/**
	 * 支付结果
	 */
	public function payResult(Request $request) {
		if (!$order_id = $request->order_id) {
			return apiReturn(20000);
		}
		$order_info = UserStockOrder::where('id', $order_id)->first();

		$goods_lists = Goods::where([
			['stock', '>', 0],
			['state', '=', 1],
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
	public function getWxStockOrderStatus(Request $request) {
		if (!$order_id = $request->order_id) {
			return apiReturn(20000);
		}

		$order_info = UserStockOrder::where('id', $order_id)->first();

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
			if ($order_info->buy_user_type == 2) {
				$response = UserStockOrder::supplementManPayDeal($message, function ($msg) {
					return false;
				});
			} elseif ($order_info->buy_user_type == 3) {
				$response = UserStockOrder::supplementAgentPayDeal($message, function ($msg) {
					return false;
				});
			}
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
	 * 再次付款
	 */
	public function againPay(Request $request) {
		if (!$order_id = $request->order_id) {
			return apiReturn(20000);
		}
		$order_info = UserStockOrder::where([
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
			$out_trade_no = 'st' . date('ymdHis') . mt_rand(10, 99);

			$order_info->out_trade_no = $out_trade_no;
			$order_result = $order_info->save();

			if ($order_result) {
				$order_goods_obj = new OrderGoods;

				$order_data = [
					'total_cash' => $order_info->pay_money,
					'out_trade_no' => $out_trade_no,
					'order_id' => $order_id,
					'order_pay_type' => $order_info->buy_user_type,
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
	 * 仓库订单列表
	 */
	public function orderLise(Request $request) {
		$uid = $request->token_info['uid'];
		$map[] = ['uid', $uid];
		if (!empty($request->stock_type)) {
			$map[] = ['deal_type', $request->stock_type];
		}
		if (!empty($request->keyword)) {
			$map[] = ['goods_name', 'like', "%{$request->keyword}%"];
		}
		// DB::enableQueryLog();
		$order_lists = UserStockRecord::where($map)
			->where('cs_user_stock_record.is_del', 1)
			->orderBy('id', 'desc')
			->groupBy('order_id', 'stock_order_id')
			->paginate(20);

		$img_url = config('app.img_url');
		foreach ($order_lists as $key => &$order_list) {
			$order_info = UserStockOrder::where('id', $order_list->stock_order_id)->first();
			$order_list->out_trade_no = $order_info->out_trade_no;
			$order_list->order_status = $order_info->order_status;
			$goods_infos = UserStockRecord::from('cs_user_stock_record as r')
				->select('g.goods_name', 'g.promotion_price', 'al.pic_cover_small')
				->join('cs_goods as g', 'r.goods_id', 'g.id')
				->leftJoin('sys_album_picture as al', 'g.picture', 'al.id')
				->where([
					'r.uid' => $uid,
					'r.shipment_type' => $order_list->shipment_type,
					'r.order_id' => $order_list->order_id,
					'r.stock_order_id' => $order_list->stock_order_id,
				])
				->get();
			foreach ($goods_infos as $key => &$goods_info) {
				$goods_info->pic_cover_small = $img_url . '/' . $goods_info->pic_cover_small;
			}
			$order_list['goods_infos'] = $goods_infos;
		}

		return apiReturn(0, 'ok', compact('order_lists'));
	}
	/**
	 * 库存详情
	 */
	public function orderDetail(Request $request) {
		$uid = $request->token_info['uid'];
		$id = $request->stock_order_id;
		$stock_order_info = UserStockOrder::where('id', $id)->first();

		$img_url = config('app.img_url');
		$stock_order_info['goods_info'] = UserStockRecord::from('cs_user_stock_record as r')
			->select('g.*', 'r.deal_num', 'al.pic_cover_small')
			->join('cs_goods as g', 'r.goods_id', 'g.id')
			->leftJoin('sys_album_picture as al', 'g.picture', 'al.id')
			->where([
				'r.uid' => $uid,
				'r.stock_order_id' => $id,
			])
			->get()
			->each(function ($item, $key) use ($img_url) {
				$item->pic_cover_small = $img_url . '/' . $item->pic_cover_small;
			});

		$stock_order_info->deal_type = $stock_order_info['goods_info'][0]['deal_type'];

		if ($stock_order_info->shipment_type = 2) {
			$user_order_info = Order::find($stock_order_info['order_id']);
		}
		return apiReturn(0, 'ok', compact('stock_order_info', 'user_order_info'));
	}

	/**
	 * 商品库存
	 */
	public function goodsStock(Request $request) {
		if (!$goods_id = $request->goods_id) {
			return apiReturn(20000);
		}
		$uid = $request->token_info['uid'];

		$stock_info = UserStock::where([
			'uid' => $uid,
			'goods_id' => $goods_id,
		])->first();

		return apiReturn(0, 'ok', compact('stock_info'));
	}

	/**
	 * 提货到线下
	 */
	public function pickGoods(Request $request) {
		if (!$request->buy_goods) {
			return apiReturn(20000);
		}
		$uid = $request->token_info['uid'];

		$sum_money = 0;
		$sum_promotion_price = 0;
		$total_num = 0;
		$stock_obj = new UserStock();
		$goods_obj = new Goods;
		$buy_goods = json_decode($request->buy_goods, true);

		foreach ($buy_goods as $key => $buy_good) {
			$stock_info = $stock_obj->where([
				'uid' => $uid,
				'goods_id' => $buy_good['goods_id'],
			])->first();
			if ($stock_info->stock_num < $buy_good['goods_num']) {
				return apiReturn(1, '库存不足');
			}
			$goods_info = $goods_obj->where('id', $buy_good['goods_id'])->first();

			$total_num += $goods_num = $buy_good['goods_num'];
			$sum_money = bcadd($sum_money, $goods_info['promotion_price'] * $goods_num, 2);
			$cash = $goods_info['promotion_price'] * $goods_num;
			$sum_promotion_price = bcadd($sum_promotion_price, $cash, 2);

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

		if ($sum_promotion_price >= $deliver_fee_info['over']['money']) {
			$deliver_fee = $deliver_fee_info['over']['fee'];
		} else {
			$deliver_fee = $deliver_fee_info['basic_fee'];
		}
		if ($deliver_fee != $request->deliver_fee) {
			return apiReturn(90011, '配送信息错误');
		}

		if ($deliver_fee != $request->total_cash) {
			return apiReturn(90015, '支付金额不正确');
		}
		$order_info = [
			'buyer_id' => $uid,
			'goods_money' => $sum_money,
			'order_money' => $deliver_fee,
			'pay_money' => $deliver_fee,
			'total_num' => $total_num,
			'deliver_fee' => $deliver_fee,
		];

		DB::beginTransaction();

		try {
			$stock_obj = new UserStock;
			$order_result = $stock_obj->addOrder($request, $order_info, $dis_goods);

			if ($order_result) {
				if ($request->total_cash == 0) {
					$message = [
						'out_trade_no' => $order_result['order_result']['out_trade_no'],
						'return_code' => 'SUCCESS',
						'result_code' => 'SUCCESS',
					];
					OrderGoods::goodsPayDeal($message, '失败');
					$pay_result = apiReturn(0, 'ok', [
						'order_id' => $order_result['order_result']['order_id'],
						'out_trade_no' => $order_result['order_result']['out_trade_no'],
					]);
				} else {
					$order_goods_obj = new OrderGoods;
					$pay_result = $order_goods_obj->payCash($request, $order_result['order_result']);
				}

				DB::commit();
			}
		} catch (\Exception $e) {
			$code = is_integer($e->getCode()) ? $e->getCode() : 90900;
			throw new ApiException($e->getMessage(), $code);
		}

		return $pay_result;
	}

	/**
	 * [delStockOrder 删除订单]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function delStockOrder(Request $request) {
		$id = $request->input('id');
		$stock_order_id = UserStockRecord::where('id', $id)->value('stock_order_id');
		$status = UserStockOrder::where('id', $stock_order_id)->value('order_status');
		if ($status != 2 || $status != 4 || $status != 13) {
			return apiReturn(1, '不能删除');
		}
		$res = UserStockRecord::where('id', $id)->update(['is_del' => 2]);
		if ($res == 1) {
			return apiReturn(0, '删除成功');
		} else {
			return apiReturn(1, '删除失败');
		}

	}
}
