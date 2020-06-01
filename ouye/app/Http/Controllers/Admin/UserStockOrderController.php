<?php

namespace App\Http\Controllers\Admin;
use App\Http\Models\UserStockOrder;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserStockOrderController extends BaseController {
	//仓库订单列表
	public function index(Request $request) {
		$perPage = 10;
		$columns = ['*'];
		$pageName = 'page';
		$currentPage = $request->input('page');
		$type = $request->input('shipment_type');
		$time = $request->input('created_at');
		$star = strtotime($time);
		$end = $star + (24 * 3600);
		$name = $request->input('nickname');
		switch ($type) {
		case '1':
			if (!empty($time) && !empty($name)) {
				$res = UserStockOrder::join('cs_user_stock_record', 'cs_user_stock_record.stock_order_id', '=', 'cs_user_stock_order.id')
					->join('cs_user', 'cs_user.id', '=', 'cs_user_stock_record.uid')
					->select('cs_user_stock_record.id', 'cs_user_stock_record.shipment_type', 'cs_user.nickname', 'cs_user_stock_record.created_at', 'cs_user_stock_record.goods_name', 'cs_user_stock_order.out_trade_no', 'cs_user_stock_record.deal_type')
					->where('cs_user.nickname', 'like', '%' . $name . '%')
					->whereBetween('cs_user_stock_record.created_at', [$star, $end])
					->where('cs_user_stock_record.shipment_type', $type)
					->where('cs_user_stock_order.buy_source', 1)
					->where('cs_user_stock_record.is_del', 1)
					->orderBy('cs_user_stock_record.created_at', 'desc')
					->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
			} elseif (!empty($time) && empty($name)) {
				$res = UserStockOrder::join('cs_user_stock_record', 'cs_user_stock_record.stock_order_id', '=', 'cs_user_stock_order.id')
					->join('cs_user', 'cs_user.id', '=', 'cs_user_stock_record.uid')
					->select('cs_user_stock_record.id', 'cs_user_stock_record.shipment_type', 'cs_user.nickname', 'cs_user_stock_record.created_at', 'cs_user_stock_record.goods_name', 'cs_user_stock_order.out_trade_no', 'cs_user_stock_record.deal_type')
					->whereBetween('cs_user_stock_record.created_at', [$star, $end])
					->where('cs_user_stock_record.shipment_type', $type)
					->where('cs_user_stock_order.buy_source', 1)
					->where('cs_user_stock_record.is_del', 1)
					->orderBy('cs_user_stock_record.created_at', 'desc')
					->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
			} elseif (empty($time) && !empty($name)) {
				$res = UserStockOrder::join('cs_user_stock_record', 'cs_user_stock_record.stock_order_id', '=', 'cs_user_stock_order.id')
					->join('cs_user', 'cs_user.id', '=', 'cs_user_stock_record.uid')
					->select('cs_user_stock_record.id', 'cs_user_stock_record.shipment_type', 'cs_user.nickname', 'cs_user_stock_record.created_at', 'cs_user_stock_record.goods_name', 'cs_user_stock_order.out_trade_no', 'cs_user_stock_record.deal_type')
					->where('cs_user.nickname', 'like', '%' . $name . '%')
					->where('cs_user_stock_record.shipment_type', $type)
					->where('cs_user_stock_order.buy_source', 1)
					->where('cs_user_stock_record.is_del', 1)
					->orderBy('cs_user_stock_record.created_at', 'desc')
					->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
			} else {
				$res = UserStockOrder::join('cs_user_stock_record', 'cs_user_stock_record.stock_order_id', '=', 'cs_user_stock_order.id')
					->join('cs_user', 'cs_user.id', '=', 'cs_user_stock_record.uid')
					->select('cs_user_stock_record.id', 'cs_user_stock_record.shipment_type', 'cs_user.nickname', 'cs_user_stock_record.created_at', 'cs_user_stock_record.goods_name', 'cs_user_stock_order.out_trade_no', 'cs_user_stock_record.deal_type')
					->where('cs_user_stock_record.shipment_type', $type)
					->where('cs_user_stock_order.buy_source', 1)
					->where('cs_user_stock_record.is_del', 1)
					->orderBy('cs_user_stock_record.created_at', 'desc')
					->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
			}

			break;
		case '2':
			if (!empty($time) && !empty($name)) {
				$res = UserStockOrder::join('cs_user_stock_record', 'cs_user_stock_record.stock_order_id', '=', 'cs_user_stock_order.id')
					->join('cs_user', 'cs_user.id', '=', 'cs_user_stock_record.uid')
					->select('cs_user_stock_record.id', 'cs_user_stock_record.shipment_type', 'cs_user.nickname', 'cs_user_stock_record.created_at', 'cs_user_stock_record.goods_name', 'cs_user_stock_order.out_trade_no', 'cs_user_stock_record.deal_type')
					->where('cs_user.nickname', 'like', '%' . $name . '%')
					->whereBetween('cs_user_stock_record.created_at', [$star, $end])
					->where('cs_user_stock_record.shipment_type', $type)
					->where('cs_user_stock_record.is_del', 1)
					->orderBy('cs_user_stock_record.created_at', 'desc')
					->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
			} elseif (!empty($time) && empty($name)) {
				$res = UserStockOrder::join('cs_user_stock_record', 'cs_user_stock_record.stock_order_id', '=', 'cs_user_stock_order.id')
					->join('cs_user', 'cs_user.id', '=', 'cs_user_stock_record.uid')
					->select('cs_user_stock_record.id', 'cs_user_stock_record.shipment_type', 'cs_user.nickname', 'cs_user_stock_record.created_at', 'cs_user_stock_record.goods_name', 'cs_user_stock_order.out_trade_no', 'cs_user_stock_record.deal_type')
					->whereBetween('cs_user_stock_record.created_at', [$star, $end])
					->where('cs_user_stock_record.shipment_type', $type)
					->where('cs_user_stock_record.is_del', 1)
					->orderBy('cs_user_stock_record.created_at', 'desc')
					->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
			} elseif (empty($time) && !empty($name)) {
				$res = UserStockOrder::join('cs_user_stock_record', 'cs_user_stock_record.stock_order_id', '=', 'cs_user_stock_order.id')
					->join('cs_user', 'cs_user.id', '=', 'cs_user_stock_record.uid')
					->select('cs_user_stock_record.id', 'cs_user_stock_record.shipment_type', 'cs_user.nickname', 'cs_user_stock_record.created_at', 'cs_user_stock_record.goods_name', 'cs_user_stock_order.out_trade_no', 'cs_user_stock_record.deal_type')
					->where('cs_user.nickname', 'like', '%' . $name . '%')
					->where('cs_user_stock_record.shipment_type', $type)
					->where('cs_user_stock_record.is_del', 1)
					->orderBy('cs_user_stock_record.created_at', 'desc')
					->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
			} else {
				$res = UserStockOrder::join('cs_user_stock_record', 'cs_user_stock_record.stock_order_id', '=', 'cs_user_stock_order.id')
					->join('cs_user', 'cs_user.id', '=', 'cs_user_stock_record.uid')
					->select('cs_user_stock_record.id', 'cs_user_stock_record.shipment_type', 'cs_user.nickname', 'cs_user_stock_record.created_at', 'cs_user_stock_record.goods_name', 'cs_user_stock_order.out_trade_no', 'cs_user_stock_record.deal_type')
					->where('cs_user_stock_record.shipment_type', $type)
					->where('cs_user_stock_record.is_del', 1)
					->orderBy('cs_user_stock_record.created_at', 'desc')
					->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
			}

			break;
		default:
			if (!empty($time) && !empty($name)) {
				$res = UserStockOrder::join('cs_user_stock_record', 'cs_user_stock_record.stock_order_id', '=', 'cs_user_stock_order.id')
					->join('cs_user', 'cs_user.id', '=', 'cs_user_stock_record.uid')
					->select('cs_user_stock_record.id', 'cs_user_stock_record.shipment_type', 'cs_user.nickname', 'cs_user_stock_record.created_at', 'cs_user_stock_record.goods_name', 'cs_user_stock_order.out_trade_no', 'cs_user_stock_record.deal_type')
					->where('cs_user.nickname', 'like', '%' . $name . '%')
					->where('cs_user_stock_record.is_del', 1)
					->whereBetween('cs_user_stock_record.created_at', [$star, $end])
					->orderBy('cs_user_stock_record.created_at', 'desc')
					->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
			} elseif (!empty($time) && empty($name)) {
				$res = UserStockOrder::join('cs_user_stock_record', 'cs_user_stock_record.stock_order_id', '=', 'cs_user_stock_order.id')
					->join('cs_user', 'cs_user.id', '=', 'cs_user_stock_record.uid')
					->select('cs_user_stock_record.id', 'cs_user_stock_record.shipment_type', 'cs_user.nickname', 'cs_user_stock_record.created_at', 'cs_user_stock_record.goods_name', 'cs_user_stock_order.out_trade_no', 'cs_user_stock_record.deal_type')
					->whereBetween('cs_user_stock_record.created_at', [$star, $end])
					->where('cs_user_stock_record.is_del', 1)
					->orderBy('cs_user_stock_record.created_at', 'desc')
					->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
			} elseif (empty($time) && !empty($name)) {
				$res = UserStockOrder::join('cs_user_stock_record', 'cs_user_stock_record.stock_order_id', '=', 'cs_user_stock_order.id')
					->join('cs_user', 'cs_user.id', '=', 'cs_user_stock_record.uid')
					->select('cs_user_stock_record.id', 'cs_user_stock_record.shipment_type', 'cs_user.nickname', 'cs_user_stock_record.created_at', 'cs_user_stock_record.goods_name', 'cs_user_stock_order.out_trade_no', 'cs_user_stock_record.deal_type')
					->where('cs_user.nickname', 'like', '%' . $name . '%')
					->where('cs_user_stock_record.is_del', 1)
					->orderBy('cs_user_stock_record.created_at', 'desc')
					->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
			} else {
				$res = UserStockOrder::join('cs_user_stock_record', 'cs_user_stock_record.stock_order_id', '=', 'cs_user_stock_order.id')
					->join('cs_user', 'cs_user.id', '=', 'cs_user_stock_record.uid')
					->select('cs_user_stock_record.id', 'cs_user_stock_record.shipment_type', 'cs_user.nickname', 'cs_user_stock_record.created_at', 'cs_user_stock_record.goods_name', 'cs_user_stock_order.out_trade_no', 'cs_user_stock_record.deal_type')
					->where('cs_user_stock_record.is_del', 1)
					->orderBy('cs_user_stock_record.created_at', 'desc')
					->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
			}
			break;
		}
		return apiReturn(0, '仓库订单列表', $res);

	}
	/**
	 * [stockOrderDetail 仓库订单详情]
	 * @param  Request $request [description]
	 * @return [type]           [json]
	 */
	public function stockOrderDetail(Request $request) {
		$id = $request->input('id');
		$res = DB::table('cs_user_stock_record')->where('id', $id)->first();
		$orderno = UserStockOrder::where('id', $res['stock_order_id'])->first();
		$seller = DB::table('cs_user')->where('id', $orderno['seller_uid'])->value('nickname');
		$user = DB::table('cs_user')->where('id', $orderno['buyer_id'])->value('nickname');
		$res['Buyer'] = $user;
		$res['seller'] = $seller;
		$res['out_trade_no'] = $orderno['out_trade_no'];

		$ordergoods = DB::table('cs_user_stock_record')->join('cs_goods', 'cs_user_stock_record.goods_id', '=', 'cs_goods.id')
			->join('sys_album_picture', 'cs_goods.picture', '=', 'sys_album_picture.id')
			->where('cs_user_stock_record.stock_order_id', $res['stock_order_id'])
			->select('sys_album_picture.pic_cover_small', 'cs_user_stock_record.goods_name', 'cs_user_stock_record.deal_num', 'cs_goods.promotion_price', 'cs_user_stock_record.goods_money', 'cs_user_stock_record.deal_type')
			->get()->toArray();
		$serve = config('app.img_url') . '/';
		foreach ($ordergoods as $k => $v) {
			$ordergoods[$k]['pic_cover_small'] = $serve . $v['pic_cover_small'];
		}

		$data = array('detail' => $res, 'ordergoods' => $ordergoods);
		return apiReturn(0, '仓库订单详情', $data);

	}

	public function stockOrderExport() {
		$name = iconv('UTF-8', 'GBK', '仓库订单表');
		Excel::create($name, function ($excel) {

			$excel->sheet('score', function ($sheet) {

				$data = UserStockOrder::join('cs_user_stock_record', 'cs_user_stock_record.stock_id', '=', 'cs_user_stock_order.id')
					->join('cs_user', 'cs_user.id', '=', 'cs_user_stock_record.uid')
					->select('cs_user_stock_order.id', 'cs_user.nickname', 'cs_user_stock_order.created_at', 'cs_user_stock_record.goods_name', 'cs_user_stock_order.out_trade_no', 'cs_user_stock_order.deal_type')
					->get()->toArray();
				$sheet->appendRow(['收件人', '下单时间', '订单名称', '订单号', '订单类型']);
				foreach ($data as $key => $value) {
					switch ($value['deal_type']) {
					case '1':
						$sheet->appendRow([
							$value['nickname'],
							date('Y-m-d H:i:s', $value['created_at']),
							$value['goods_name'],
							$value['out_trade_no'],
							'入库',
						]);
						break;
					default:
						$sheet->appendRow([
							$value['nickname'],
							date('Y-m-d H:i:s', $value['created_at']),
							$value['goods_name'],
							$value['out_trade_no'],
							'出库',
						]);
						break;
					}

				}

			});

		})->export('xls');
	}

}
