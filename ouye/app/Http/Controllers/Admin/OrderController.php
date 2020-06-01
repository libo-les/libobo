<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;  
use App\Http\Models\Order;
use App\Http\Models\OrderAction;
use Illuminate\Support\Facades\DB;
use EasyWeChat\Factory;
use Excel;
use App\Http\Models\UserStockOrder;
/**
 * 订单管理
 */
class OrderController extends BaseController
{	
	/**
	 * [index 订单列表]
	 * @param  Request $request [description]
	 * @return [type]           [json]
	 */
	public function index(Request $request)
	{

		$status = $request->input('orderStatus');
		$time = $request->input('created_at');
		$star=strtotime($time);
		$end = $star+(24*3600);
		$name = $request->input('user_name');
		$perPage = 10;
		$columns = ['*'];
		$pageName = 'page';
		$currentPage = $request->input('page');
		if (!empty($status)&&empty($time)&&empty($name)) {
			
			$res = order::join('cs_order_goods','cs_order_goods.order_id','=','cs_order.id')
			->select('cs_order.receiver_name','cs_order.created_at','cs_order_goods.goods_name','cs_order.out_trade_no','cs_order.order_status','cs_order.id')
			->where('cs_order.order_status',$status)
			->where('is_deleted_shop',0)
			->orderBy('cs_order.created_at','desc')
			->paginate($perPage, $columns, $pageName, $currentPage)->toArray();

		}elseif (empty($status)&&!empty($time)&&empty($name)) {		
			
			$res = order::join('cs_order_goods','cs_order_goods.order_id','=','cs_order.id')
			->select('cs_order.receiver_name','cs_order.created_at','cs_order_goods.goods_name','cs_order.out_trade_no','cs_order.order_status','cs_order.id')
			->whereBetween('cs_order.created_at',[$star,$end])
			->where('is_deleted_shop',0)
			->orderBy('cs_order.created_at','desc')
			->paginate($perPage, $columns, $pageName, $currentPage)->toArray();

		}elseif (empty($status)&&empty($time)&&!empty($name)) {
			
			$res = order::join('cs_order_goods','cs_order_goods.order_id','=','cs_order.id')
			->select('cs_order.receiver_name','cs_order.created_at','cs_order_goods.goods_name','cs_order.out_trade_no','cs_order.order_status','cs_order.id')
			->where('receiver_name','like','%'.$name.'%')
			->where('is_deleted_shop',0)
			->orderBy('cs_order.created_at','desc')
			->paginate($perPage, $columns, $pageName, $currentPage)->toArray();

		}elseif (empty($status)&&!empty($time)&&!empty($name)) {
			
			$res = order::join('cs_order_goods','cs_order_goods.order_id','=','cs_order.id')
			->select('cs_order.receiver_name','cs_order.created_at','cs_order_goods.goods_name','cs_order.out_trade_no','cs_order.order_status','cs_order.id')
			->where('receiver_name','like','%'.$name.'%')
			->where('is_deleted_shop',0)
			->orderBy('cs_order.created_at','desc')
			->whereBetween('cs_order.created_at',[$star,$end])
			->paginate($perPage, $columns, $pageName, $currentPage)->toArray();

		}elseif (!empty($status)&&!empty($time)&&empty($name)) {
			$res = order::join('cs_order_goods','cs_order_goods.order_id','=','cs_order.id')
			->select('cs_order.receiver_name','cs_order.created_at','cs_order_goods.goods_name','cs_order.out_trade_no','cs_order.order_status','cs_order.id')
			->where('cs_order.order_status',$status)
			->where('is_deleted_shop',0)
			->orderBy('cs_order.created_at','desc')
			->whereBetween('cs_order.created_at',[$star,$end])
			->paginate($perPage, $columns, $pageName, $currentPage)->toArray();


		}elseif (!empty($status)&&!empty($time)&&!empty($name)) {
			$res = order::join('cs_order_goods','cs_order_goods.order_id','=','cs_order.id')
			->select('cs_order.receiver_name','cs_order.created_at','cs_order_goods.goods_name','cs_order.out_trade_no','cs_order.order_status','cs_order.id')
			->where('cs_order.order_status',$status)
			->where('receiver_name','like','%'.$name.'%')
			->whereBetween('cs_order.created_at',[$star,$end])
			->where('is_deleted_shop',0)
			->orderBy('cs_order.created_at','desc')
			->paginate($perPage, $columns, $pageName, $currentPage)->toArray();

		}else{
			
			$res = order::join('cs_order_goods','cs_order_goods.order_id','=','cs_order.id')
			->select('cs_order.receiver_name','cs_order.created_at','cs_order_goods.goods_name','cs_order.out_trade_no','cs_order.order_status','cs_order.id')
			->orderBy('cs_order.created_at','desc')
			->where('is_deleted_shop',0)
			->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
		}		
		if (!empty($res)) {
			return apiReturn(0,'ok',$res);
		}else{
			return apiReturn(1,'暂无数据');

		}
		
	}
	/**
	 * [delivery 发货]
	 * @param  Request $request [description]
	 * @return [type]           [json]
	 */
	public function deliverGoods(Request $request)
	{
		$id= $request->input('id');
		$mas = order::where('id',$id)->first()->toArray();
		$res = Order::where('id',$id)->update(['order_status' =>5,'delivery_status'=>3,'consign_time'=>time()]);
		if ($mas['stock_type'] > 1) {
			$user_stock_order_info = UserStockOrder::where('order_id', $id)->first();
			$user_stock_order_info->order_status = 5;
			$user_stock_order_info->save();
		}

		if ($res==1) {
			$action = OrderAction::insert([
				'order_id'=>$id,
				'action_user'=>2,
				'action_uid'=>$mas['buyer_id'],
				'order_status'=>5,
				'status_desc'=>'已发货',
				'action_note'=>'已发货',
				'pay_status'=>2,
				'created_at'=>time(),
				'updated_at'=>time(),
				]);
			return apiReturn(0,'ok');
		}else{
			return apiReturn(1,'no');
		}
		
	}
	/**
	 * [refuseOrder 拒绝接单]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function refuseOrder(Request $request)
	{	
		$id= $request->input('id');
		$note = $request->input('action_note');
		$mas = order::where('id',$id)->first()->toArray();
		$ordergoods = DB::table('cs_order_goods')->where('order_id',$id)->first();
		$Admin =DB::table('sys_admin')->where('id',session('admin_id'))->first();
		$goodstock = DB::table('cs_goods')->where('id',$ordergoods['goods_id'])->first();
		$out_trade_no = 'r'.$mas['out_trade_no'];
		$returnback = $this->wechatRetrun($mas['out_trade_no'],$out_trade_no,$mas['pay_money'],$mas['pay_money']);
		
		if ($returnback['result_code']!='SUCCESS'||$returnback['return_code']!='SUCCESS') {
			
			return apiReturn(1,'退款失败');
		}

		//修改订单表订单状态
		$res = order::where('id',$id)->update(['order_status'=>10,'refund_status'=>7]);
		if ($res==1) {

			//订单动作记录插入
			$action = OrderAction::insert([
				'order_id'=>$id,
				'action_user'=>2,
				'action_uid'=>$mas['buyer_id'],
				'order_status'=>5,
				'status_desc'=>'拒绝接单并退款',
				'action_note'=>$note,
				'pay_status'=>2,
				'created_at'=>time(),
				'updated_at'=>time()
			]);
			//订单退款账户记录插入
			$refund=DB::table('cs_order_refund')->insert([
				'refund_trade_no'=>$out_trade_no,
				'refund_status'=>7,
				'refund_money'=>$mas['pay_money'],
				'refund_way'=>1,
				'buyer_id'=>$mas['buyer_id'],
				'shop_refuse_reason'=>$note,
				'created_at'=>time(),
				'updated_at'=>time()
			]);

			//修改订单商品表
			$updordergoods = DB::table('cs_order_goods')->where('order_id',$id)->update([
				'order_status'=>10,
				'refund_status'=>7,
				'refund_real_money'=>$mas['pay_money'],
				'memo'=>'拒绝接单'
			]);

			//订单商品退货退款操作表
			$refund_action = DB::table('cs_order_refund_action')->insert([
				'order_id' => $id,
				'refund_status'=>7,
				'action'=>'拒绝接单',
				'action_way'=>2,
				'action_userid'=>$Admin['id'],
				'action_username'=>$Admin['user_name'],
				'seller_memo'=>$note,
				'created_at'=>time(),
				'updated_at'=>time()
			]);
				if($ordergoods['stock_type']==1){
					//商品表库存修改
					DB::table('cs_goods')->where('id',$ordergoods['goods_id'])->update([
						'stock'=>$goodstock['stock']+$ordergoods['num']
					]);
				}elseif($ordergoods['stock_type']==2||$ordergoods['stock_type']==3){
							//修改代理表库存
					$agent = DB::table('cs_agent')->where('uid',$mas['buyer_id'])->first();
					if (count($agent)!=0) {
						$updAgent = DB::table('cs_agent')->where('uid',$mas['buyer_id'])->update([
							'stock_num'=>$agent['stock_num']+$ordergoods['stock_buy_num'],
						]);
					}
					
					//修改商品表库存
					$goodsstock = DB::table('cs_goods')->where('id',$ordergoods['goods_id'])->update(['stock'=>$goodstock['stock']+$ordergoods['num']-$ordergoods['stock_buy_num']]);

					//用户库存
					$user_stock = DB::table('cs_user_stock')->where('uid',$ordergoods['buyer_id'])->where('goods_id',$ordergoods['goods_id'])->first();
					$userstock = DB::table('cs_user_stock')->where('id',$ordergoods['stock_id'])->where('goods_id',$ordergoods['goods_id'])->update([
						'stock_num'=>$user_stock['stock_num']+$ordergoods['stock_buy_num'],
						
					]);
					//仓库订单
					$stock_order = DB::table('cs_user_stock_order')->where('id',$user_stock['id'])->update([
						'order_status'=>10,
					]);
					$usertype = DB::table('cs_user')->where('id',$ordergoods['buyer_id'])->value('user_type');
					//用户库存记录
					$stock_record = DB::table('cs_user_stock_record')->where('id',$user_stock['id'])->update([
						'status'=>2,
						'buy_source'=>DB::table('cs_order')->where('id',$ordergoods['order_id'])->value('stock_type'),
						'buy_stock_type'=>$ordergoods['stock_type'],
						'buy_stock_num'=>$ordergoods['stock_buy_num'],
						'stock_num'=>$user_stock['stock_num'],
						'deal_type'=>1,
						'deal_num'=>$ordergoods['stock_buy_num'],
						'goods_name'=>$ordergoods['goods_name'],
						'goods_money'=>$mas['goods_money'],
						'pay_money'=>$mas['pay_money'],
						'shipment_type'=>1,		
					]);
				}

			
			return apiReturn(0,'ok');
		}else{
			return apiReturn(1,'no');
		}
	}




	/**
	 * [delorder 删除订单]
	 * @param  Request $request [description]
	 * @return [type]           [json]
	 */
	public function delorder(Request $request)
	{
		$id= $request->input('id');
		$res = Order::where('id',$id)->update(['is_deleted_shop'=>1]);
		if ($res==1) {
			return apiReturn(0,'操作成功！');
		}else{
			return apiReturn(1,'操作失败！');
		}
		
		
	}
	/**
	 * [orderDetails 订单详情]
	 * @param  Request $request [description]
	 * @return [type]           [json]
	 */
	public function orderDetails(Request $request)
	{
		$id = $request->input('id');
		$refund = DB::table('cs_order_refund_action')->where('order_id',$id)->value('buyer_message');
		//订单信息
		$res = order::where('cs_order.id',$id)
		->select(
		'cs_order.id',
		'cs_order.receiver_name',
		'cs_order.receiver_province',
		'cs_order.receiver_city',
		'cs_order.receiver_district',
		'cs_order.receiver_address',
		'cs_order.receiver_mobile',
		'cs_order.order_status',
		'cs_order.refund_status',
		'cs_order.out_trade_no',
		'cs_order.goods_money',
		'cs_order.order_money',
		'cs_order.pay_money',
		'cs_order.delivery_money',
		'cs_order.delivery_status',
		'cs_order.buyer_message'
		)
		->first()->toArray();
		$res['refund_buyer_message']=$refund;
		//商品清单
		$goodorder = DB::table('cs_order_goods')->join('cs_order','cs_order.id','=','cs_order_goods.order_id')
		->join('sys_album_picture','cs_order_goods.goods_picture','=','sys_album_picture.id')
		->select('cs_order_goods.goods_name','cs_order_goods.promotion_price','cs_order_goods.num','cs_order.goods_money','sys_album_picture.pic_cover_small')
		->where('cs_order.id',$id)
		->first();		
		$goodorder['pic_cover_small']  = config('app.img_url').'/'.$goodorder['pic_cover_small'];
		$data=array('Details'=>$res,'order_goods'=>$goodorder);
		return apiReturn(0,'ok',$data);
	}

	/**
	 * [refund 是否接受退款]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function refund(Request $request)
	{

		$id = $request->input('id');
		$status = $request->input('order_status');
		$note = $request->input('action_note');

		$mas = order::where('id',$id)->first()->toArray();
		
		$orderGoods = DB::table('cs_order_goods')->where('order_id',$id)->first();
		
		if ($status==9) {
			$res = order::where('id',$id)->update(['order_status'=>$status,'refund_status'=>2]);
		}else{
			$res = order::where('id',$id)->update(['order_status'=>$status,'refund_status'=>6]);
		}
		
		if ($res==1) {
			if ($status==9) {
				$action = OrderAction::insert([
					'order_id'=>$id,
					'action_user'=>2,
					'action_uid'=>$mas['buyer_id'],
					'order_status'=>9,
					'status_desc'=>'接受退款申请',
					'action_note'=>'接受退款申请',
					'pay_status'=>2,
					'created_at'=>time(),
					'updated_at'=>time()
				]);

				$cs_order_goods = DB::table('cs_order_goods')->where('order_id',$id)->update([
					'order_status'=>9,
					'refund_require_money'=>$mas['pay_money'],
					'refund_status'=>2,
					'memo'=>'商家同意退款请求'
				]);


			}else{
				$action = OrderAction::insert(
					['order_id'=>$id,
					'action_user'=>2,
					'action_uid'=>$mas['buyer_id'],
					'order_status'=>$status,
					'status_desc'=>'拒绝退款',
					'action_note'=>'拒绝退款',
					'pay_status'=>2,
					'created_at'=>time(),
					'updated_at'=>time()
				]);
			}
			return apiReturn(0,'ok');
		}else{
			return apiReturn(1,'no');
		}

	}

	/**
	 * [takeGooes 确认收货]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function takeGooes(Request $request)
	{
		$id = $request->input('id');
		$res = order::where('id',$id)->update(['refund_status'=>4]);
		$mas = order::where('id',$id)->first()->toArray();
		$admin = DB::table('sys_admin')->where('id',session('admin_id'))->first();
		if ($res==1) {
			$action = OrderAction::insert([
				'order_id'=>$id,
				'action_user'=>2,
				'action_uid'=>session('admin_id'),
				'order_status'=>9,
				'status_desc'=>'商家确认收货',
				'action_note'=>'商家确认收货',
				'pay_status'=>2,
				'created_at'=>time(),
				'updated_at'=>time()
			]);
			//订单商品退货退款操作表
			$refund_action = DB::table('cs_order_refund_action')->where('order_id',$id)->update([	
				'refund_status'=>4,
				'action'=>'商家确认收货',
				'action_way'=>2,
				'action_userid'=>session('admin_id'),
				'action_username'=>$admin['real_name'],
				'seller_memo'=>'商家确认收货'
			]);
			//订单退款账户记录
			$refund = DB::table('cs_order_refund')->where('order_id',$id)->update([
				'refund_status'=>4,
				'shop_remark'=>'商家确认收货',
				'updated_at'=>time()
			]);
			//订单商品
			$updordergoods = DB::table('cs_order_goods')->where('order_id',$id)->update([
				'order_status'=>9,
				'refund_status'=>4,
				'refund_real_money'=>$mas['pay_money'],
				'memo'=>'商家确认收货'
			]);
			

			return apiReturn(0,'ok');
		}else{
			return apiReturn(1,'no');
		}	

	}
	/**
	 * [isDetermine 是否确认退款]
	 * @param  Request $request [description]
	 * @return boolean          [description]
	 */
	public function isDetermine(Request $request)
	{
		$id = $request->input('id');
		$status = $request->input('refund_status');
		$note = $request->input('action_note');
		$mas = order::where('id',$id)->first()->toArray();
		$ordergoods = DB::table('cs_order_goods')->where('order_id',$id)->first();
		$Admin =DB::table('sys_admin')->where('id',session('admin_id'))->first();
		$goodstock = DB::table('cs_goods')->where('id',$ordergoods['goods_id'])->first();
		$out_trade_no = 'r'.$mas['out_trade_no'];//退款单
		if ($status==4){
			//确认退款
			$res =order::where('id',$id)->update(['refund_status'=>$status,'order_status'=>9]);
			if ($res==1) {
				$action = OrderAction::insert([
					'order_id'=>$id,
					'action_user'=>2,
					'action_uid'=>$mas['buyer_id'],
					'order_status'=>9,
					'status_desc'=>'确认退款',
					'action_note'=>'确认退款',
					'pay_status'=>2,
					'created_at'=>time(),
					'updated_at'=>time()
				]);
				//订单退款账户记录插入
				$refund=DB::table('cs_order_refund')->insert([
					'refund_trade_no'=>$out_trade_no,
					'refund_status'=>4,
					'order_id'=>$id,
					'refund_money'=>$mas['pay_money'],
					'refund_way'=>1,
					'buyer_id'=>$mas['buyer_id'],
					'shop_refuse_reason'=>$note,
					'created_at'=>time(),
					'updated_at'=>time()
				]);
				//修改订单商品表
				$updordergoods = DB::table('cs_order_goods')->where('order_id',$id)->update([
					'order_status'=>9,
					'refund_status'=>4,
					'refund_real_money'=>$mas['pay_money'],
					'memo'=>'商家确认退款'
				]);
				//订单商品退货退款操作表
				$refund_action = DB::table('cs_order_refund_action')->insert([
					'order_id' => $id,
					'refund_status'=>4,
					'action'=>'收货，确认退款',
					'action_way'=>2,
					'action_userid'=>$Admin['id'],
					'action_username'=>$Admin['user_name'],
					'created_at'=>time(),
					'updated_at'=>time()
				]);
				if($ordergoods['stock_type']==1){
					//商品表库存修改
					DB::table('cs_goods')->where('id',$ordergoods['goods_id'])->update([
						'stock'=>$goodstock['stock']+$ordergoods['num']
					]);
				}elseif($ordergoods['stock_type']==2||$ordergoods['stock_type']==3){
							//修改代理表库存
					$agent = DB::table('cs_agent')->where('uid',$mas['buyer_id'])->first();
					if (count($agent)!=0) {
						$updAgent = DB::table('cs_agent')->where('uid',$mas['buyer_id'])->update([
							'stock_num'=>$agent['stock_num']+$ordergoods['stock_buy_num'],
						]);
					}
					
					//修改商品表库存
					$goodsstock = DB::table('cs_goods')->where('id',$ordergoods['goods_id'])->update(['stock'=>$goodstock['stock']+$ordergoods['num']-$ordergoods['stock_buy_num']]);

					//用户库存
					$user_stock = DB::table('cs_user_stock')->where('uid',$ordergoods['buyer_id'])->where('goods_id',$ordergoods['goods_id'])->first();
					$userstock = DB::table('cs_user_stock')->where('id',$ordergoods['stock_id'])->where('goods_id',$ordergoods['goods_id'])->update([
						'stock_num'=>$user_stock['stock_num']+$ordergoods['stock_buy_num'],
						
					]);
					//仓库订单
					$stock_order = DB::table('cs_user_stock_order')->where('id',$user_stock['id'])->update([
						'order_status'=>10,
					]);
					$usertype = DB::table('cs_user')->where('id',$ordergoods['buyer_id'])->value('user_type');
					//用户库存记录
					$stock_record = DB::table('cs_user_stock_record')->where('id',$user_stock['id'])->update([
						'status'=>2,
						'buy_source'=>DB::table('cs_order')->where('id',$ordergoods['order_id'])->value('stock_type'),
						'buy_stock_type'=>$ordergoods['stock_type'],
						'buy_stock_num'=>$ordergoods['stock_buy_num'],
						'stock_num'=>$user_stock['stock_num'],
						'deal_type'=>1,
						'deal_num'=>$ordergoods['stock_buy_num'],
						'goods_name'=>$ordergoods['goods_name'],
						'goods_money'=>$mas['goods_money'],
						'pay_money'=>$mas['pay_money'],
						'shipment_type'=>1,		
					]);
				}

				/***************************************************************************************************************/
				//开始退款
				
				$returnback = $this->wechatRetrun($mas['out_trade_no'],$out_trade_no,$mas['pay_money'],$mas['pay_money']-$mas['delivery_money']);
				
				if ($returnback['result_code']!='SUCCESS'||$returnback['return_code']!='SUCCESS') {
					//退款失败
					$res =order::where('id',$id)->update(['refund_status'=>8,'order_status'=>11]);
					//订单动作记录
						$action = OrderAction::insert([
							'order_id'=>$id,
							'action_user'=>2,
							'action_uid'=>$mas['buyer_id'],
							'order_status'=>11,
							'status_desc'=>'退款失败',
							'action_note'=>'退款失败',
							'pay_status'=>2,
							'created_at'=>time(),
							'updated_at'=>time()
						]);
				//订单退款账户记录插入
						$refund=DB::table('cs_order_refund')->insert([
							'order_id'=>$id,
							'refund_trade_no'=>$out_trade_no,
							'refund_status'=>8,
							'refund_money'=>$mas['pay_money'],
							'refund_way'=>1,
							'buyer_id'=>$mas['buyer_id'],
							'shop_remark'=>$note,
							'created_at'=>time(),
							'updated_at'=>time()
						]);
				//修改订单商品表
						$updordergoods = DB::table('cs_order_goods')->where('order_id',$id)->update([
							'order_status'=>11,
							'refund_status'=>8,
							'refund_real_money'=>$mas['pay_money'],
							'memo'=>'退款失败'
						]);
				//订单商品退货退款操作表
						$refund_action = DB::table('cs_order_refund_action')->insert([
							'order_id' => $id,
							'refund_status'=>8,
							'action'=>'退款失败',
							'action_way'=>2,
							'action_userid'=>$Admin['id'],
							'action_username'=>$Admin['user_name'],
							'created_at'=>time(),
							'updated_at'=>time()
						]);

					return apiReturn(1,'退款失败');
				}else{
					//退款成功
					$res =order::where('id',$id)->update(['refund_status'=>5,'order_status'=>10]);
					if ($res==1) {
					//订单动作记录
						$action = OrderAction::insert([
							'order_id'=>$id,
							'action_user'=>2,
							'action_uid'=>$mas['buyer_id'],
							'order_status'=>10,
							'status_desc'=>'退款成功',
							'action_note'=>'退款成功',
							'pay_status'=>2,
							'created_at'=>time(),
							'updated_at'=>time()
						]);
				//订单退款账户记录插入
						$refund=DB::table('cs_order_refund')->insert([
							'refund_trade_no'=>$out_trade_no,
							'refund_status'=>5,
							'refund_money'=>$mas['pay_money'],
							'refund_way'=>1,
							'buyer_id'=>$mas['buyer_id'],
							'shop_remark'=>$note,
							'created_at'=>time(),
							'updated_at'=>time()
						]);
				//修改订单商品表
						$updordergoods = DB::table('cs_order_goods')->where('order_id',$id)->update([
							'order_status'=>10,
							'refund_status'=>5,
							'refund_real_money'=>$mas['pay_money'],
							'memo'=>'退款成功'

						]);
				//订单商品退货退款操作表
						$refund_action = DB::table('cs_order_refund_action')->insert([
							'order_id' => $id,
							'refund_status'=>5,
							'action'=>'退款成功',
							'action_way'=>2,
							'action_userid'=>$Admin['id'],
							'action_username'=>$Admin['user_name'],
							'created_at'=>time(),
							'updated_at'=>time()
						]);
						return apiReturn(0,'退款成功');
					}else{
						return apiReturn(1,'操作失败');
					}	
				}
			}	
		}else{
			//拒绝退款
			$res =order::where('id',$id)->update(['refund_status'=>$status,'order_status'=>11]);
			if ($res==1) {
				
				//订单动作
				$action = OrderAction::insert([
					'order_id'=>$id,
					'action_user'=>2,
					'action_uid'=>$mas['buyer_id'],
					'order_status'=>11,
					'status_desc'=>'拒绝退款',
					'action_note'=>'拒绝退款',
					'pay_status'=>2,
					'created_at'=>time(),
					'updated_at'=>time()
				]);
				//订单退款账户记录插入
				$refund=DB::table('cs_order_refund')->insert([
					'refund_trade_no'=>$out_trade_no,
					'refund_status'=>6,
					'refund_money'=>$mas['pay_money'],
					'refund_way'=>1,
					'buyer_id'=>$mas['buyer_id'],
					'shop_refuse_reason'=>$note,
					'created_at'=>time(),
					'updated_at'=>time()
				]);
				//修改订单商品表
				$updordergoods = DB::table('cs_order_goods')->where('order_id',$id)->update([
					'order_status'=>11,
					'refund_real_money'=>$mas['pay_money'],
					'memo'=>'商家绝退款'
				]);
				//订单商品退货退款操作表
				$refund_action = DB::table('cs_order_refund_action')->insert([
					'order_id' => $id,
					'refund_status'=>6,
					'action'=>'拒绝退款',
					'action_way'=>2,
					'action_userid'=>$Admin['id'],
					'action_username'=>$Admin['user_name'],
					'seller_memo'=>$note,
					'created_at'=>time(),
					'updated_at'=>time()
				]);
				if($ordergoods['stock_type']==1){
					//商品表库存修改
					DB::table('cs_goods')->where('id',$ordergoods['goods_id'])->update([
						'stock'=>$goodstock['stock']+$ordergoods['num']
					]);
				}elseif($ordergoods['stock_type']==2||$ordergoods['stock_type']==3){
							//修改代理表库存
					$agent = DB::table('cs_agent')->where('uid',$mas['buyer_id'])->first();
					if (count($agent)!=0) {
						$updAgent = DB::table('cs_agent')->where('uid',$mas['buyer_id'])->update([
							'stock_num'=>$agent['stock_num']+$ordergoods['stock_buy_num'],
						]);
					}
					
					//修改商品表库存
					$goodsstock = DB::table('cs_goods')->where('id',$ordergoods['goods_id'])->update(['stock'=>$goodstock['stock']+$ordergoods['num']-$ordergoods['stock_buy_num']]);

					//用户库存
					$user_stock = DB::table('cs_user_stock')->where('uid',$ordergoods['buyer_id'])->where('goods_id',$ordergoods['goods_id'])->first();
					$userstock = DB::table('cs_user_stock')->where('id',$ordergoods['stock_id'])->where('goods_id',$ordergoods['goods_id'])->update([
						'stock_num'=>$user_stock['stock_num']+$ordergoods['stock_buy_num'],
						
					]);
					//仓库订单
					$stock_order = DB::table('cs_user_stock_order')->where('id',$user_stock['id'])->update([
						'order_status'=>10,
					]);
					$usertype = DB::table('cs_user')->where('id',$ordergoods['buyer_id'])->value('user_type');
					//用户库存记录
					$stock_record = DB::table('cs_user_stock_record')->where('id',$user_stock['id'])->update([
						'status'=>2,
						'buy_source'=>DB::table('cs_order')->where('id',$ordergoods['order_id'])->value('stock_type'),
						'buy_stock_type'=>$ordergoods['stock_type'],
						'buy_stock_num'=>$ordergoods['stock_buy_num'],
						'stock_num'=>$user_stock['stock_num'],
						'deal_type'=>1,
						'deal_num'=>$ordergoods['stock_buy_num'],
						'goods_name'=>$ordergoods['goods_name'],
						'goods_money'=>$mas['goods_money'],
						'pay_money'=>$mas['pay_money'],
						'shipment_type'=>1,		
					]);
				}
				return apiReturn(0,'操作成功');
			}else{
				return apiReturn(1,'操作失败');
			}

		}	
	
		
	}

	/**
	 * [wechatRetrun 微信退款]
	 * @param  [type] $number       [商户订单号]
	 * @param  [type] $refundNumber [商户退款单号]
	 * @param  [type] $totalFee     [订单金额]
	 * @param  [type] $refundFee    [退款金额]
	 * @return [type]               [json]
	 * $number, $refundNumber, $totalFee, $refundFee, $config = []
	 */
	public function wechatRetrun($number,$refundNumber,$totalFee,$refundFee)
	{	
		$dir = dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/resources/certificate/1520797141';
		$mch_config = config('wx_config.service_provider.1520797141');
		$options = [
	                // 必要配置
				'app_id'             => $mch_config['app_id'],
	            'key'                => $mch_config['key'],   // API 密钥
	            'mch_id'             => $mch_config['mch_id'],
	            'cert_path'          => $dir.'/apiclient_cert.pem', //api 证书路径
	            'key_path'           => $dir.'/apiclient_key.pem',     
	            'notify_url'         => url("admin/order/wechatRetrun"), 

	        ];
	        $app = Factory::payment($options);
	        $app->setSubMerchant(config('wx_config.mch_id'), config('wx_config.appid'));
	        $sel=$app->order->queryByOutTradeNumber($number);//查询订单状态
	      	
			//根据商户订单号退款
	        $result= $app->refund->byOutTradeNumber($number,$refundNumber,floor($totalFee * 100),floor($refundFee * 100),  $config = [
	        	'refund_desc' => '退款',
				'notify_url'  => url("Admin/Order/wechatRetrun"), 
					
			]);
			
	        return $result;
	        
	    }

	    public function orderExport()
	    {
	    	$name = iconv('UTF-8', 'GBK', '用户订单表');
	    	Excel::create($name,function($excel){

	    		$excel->sheet('score', function($sheet){

	    			$data = order::join('cs_order_goods','cs_order_goods.order_id','=','cs_order.id')
	    			->select('cs_order.receiver_name','cs_order.created_at','cs_order_goods.goods_name','cs_order.out_trade_no','cs_order.order_status','cs_order.id')
	    			->orderBy('cs_order.created_at','desc')
	    			->where('is_deleted_shop',0)
	    			->get()->toArray();
	    			$sheet->appendRow(['收件人','下单时间','订单名称','订单号','订单状态']);
	    			foreach ($data as $key => $value) {
	    				switch ($value['order_status']) {
	    					case '1':
	    					$sheet->appendRow([
	    						$value['receiver_name'],
	    						date('Y-m-d H:i:s',$value['created_at']),
	    						$value['goods_name'],
	    						$value['out_trade_no'],
	    						'创建订单,等待支付'
	    					]);
	    					break;
	    					case '2':
	    						$sheet->appendRow([
	    						$value['receiver_name'],
	    						date('Y-m-d H:i:s',$value['created_at']),
	    						$value['goods_name'],
	    						$value['out_trade_no'],
	    						'取消支付'
	    					]);
	    						break;
	    					case '3':
	    						$sheet->appendRow([
	    						$value['receiver_name'],
	    						date('Y-m-d H:i:s',$value['created_at']),
	    						$value['goods_name'],
	    						$value['out_trade_no'],
	    						'已付款,等待核销,待接单'
	    					]);	
    							break;	
    						case '4':
    							$sheet->appendRow([
	    						$value['receiver_name'],
	    						date('Y-m-d H:i:s',$value['created_at']),
	    						$value['goods_name'],
	    						$value['out_trade_no'],
	    						'支付失败'
	    					]);	
    							break;
    						case '5':
    						    $sheet->appendRow([
	    						$value['receiver_name'],
	    						date('Y-m-d H:i:s',$value['created_at']),
	    						$value['goods_name'],
	    						$value['out_trade_no'],
	    						'已发货'
	    					]);									
    						    break;
    						case '6':
    							$sheet->appendRow([
	    						$value['receiver_name'],
	    						date('Y-m-d H:i:s',$value['created_at']),
	    						$value['goods_name'],
	    						$value['out_trade_no'],
	    						'已收货'
	    					]);		
								break;
							case '7':
								$sheet->appendRow([
	    						$value['receiver_name'],
	    						date('Y-m-d H:i:s',$value['created_at']),
	    						$value['goods_name'],
	    						$value['out_trade_no'],
	    						'已评价'
	    					]);	
								break;	
							case '8':
								$sheet->appendRow([
	    						$value['receiver_name'],
	    						date('Y-m-d H:i:s',$value['created_at']),
	    						$value['goods_name'],
	    						$value['out_trade_no'],
	    						'申请退款'
	    					]);	
								break;	
							case '9':
								$sheet->appendRow([
	    						$value['receiver_name'],
	    						date('Y-m-d H:i:s',$value['created_at']),
	    						$value['goods_name'],
	    						$value['out_trade_no'],
	    						'退款中'
	    					]);	
								break;	
							case '10':
								$sheet->appendRow([
	    						$value['receiver_name'],
	    						date('Y-m-d H:i:s',$value['created_at']),
	    						$value['goods_name'],
	    						$value['out_trade_no'],
	    						'退款成功,拒绝接单'
	    					]);	
								break;	
							case '11':
								$sheet->appendRow([
	    						$value['receiver_name'],
	    						date('Y-m-d H:i:s',$value['created_at']),
	    						$value['goods_name'],
	    						$value['out_trade_no'],
	    						'退款失败,拒绝退款'
	    					]);	
								break;	
							case '12':
								$sheet->appendRow([
	    						$value['receiver_name'],
	    						date('Y-m-d H:i:s',$value['created_at']),
	    						$value['goods_name'],
	    						$value['out_trade_no'],
	    						'分次核销'
	    					]);	
								break;					
	    					default:
                 			$sheet->appendRow([
	    						$value['receiver_name'],
	    						date('Y-m-d H:i:s',$value['created_at']),
	    						$value['goods_name'],
	    						$value['out_trade_no'],
	    						'交易成功'
	    					]);	
	    					break;
	    				}

	    			}     

	    		});

	    	})->export('xls');
	    }

	}
















