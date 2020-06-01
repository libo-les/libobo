<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

/**
 * 首页
 */
class HomeController extends BaseController
{

	/**
	 * [index 首页数据统计]
	 * @return [type] [json]
	 */
	public function index()
	{
		$today = strtotime(date('Y-m-d'));
		$todayend = $today+3600*24;
		$lastweek = $today-3600*24*7;
		//今日订单总金额
		$todaymoney = DB::table('cs_order')->whereBetween('created_at',[$today,$todayend])->whereIn('order_status',[3,5,6,7,13])->sum('pay_money');
		//订单总笔数
		$total = DB::table('cs_order')->whereBetween('created_at',[$today,$todayend])->count('*');
		//库存预警
		$early = DB::table('cs_goods')->select('stock','min_stock_alarm')->get()->toArray();
		$warn = DB::table('sys_config')->where('key','goods_warn')->value('value');
		$count=0;
		foreach ($early as $key => $value) {
			if ($value['stock']<=$warn) {
				$count = $count+1;
			}else{
				continue;
			}
		}
		//昨日订单销量
		$yesterday = $today-3600*24;
		$yestersale = DB::table('cs_order')->whereIn('order_status',[3,5,6,7,13])->whereBetween('created_at',[$yesterday,$today])->count('*');
		//昨日订单金额
		$yesmoney =  DB::table('cs_order')->whereIn('order_status',[3,5,6,7,13])->whereBetween('created_at',[$yesterday,$today])->sum('pay_money');
		//上周订单销量
		$lastweeksale = DB::table('cs_order')->whereIn('order_status',[3,5,6,7,13])->whereBetween('created_at',[$lastweek,$today])->count('*');
		//上周订单金额
		$lastweekMoney = DB::table('cs_order')->whereIn('order_status',[3,5,6,7,13])->whereBetween('created_at',[$lastweek,$today])->sum('pay_money');
		
		//订单列表
		$orderlist = DB::table('cs_order')->orderBy('updated_at','desc')->limit(4)->get()->toArray();
		
		foreach ($orderlist as $key => $value) {
			$orderlist[$key]['minute'] =ceil((time()-$value['updated_at'])/60);
		}
		
		foreach ($orderlist as $key => $value) {
			if ($value['minute']>60&&$value['minute']<1440) {
				$orderlist[$key]['hours'] = floor($value['minute']/(60)).'小时';
				unset($orderlist[$key]['minute']);
			}elseif($value['minute']>1440){
				$orderlist[$key]['day'] = floor($value['minute']/(60*24)).'天';
				unset($orderlist[$key]['minute']);
			}else{
				$orderlist[$key]['minute'] = $value['minute'].'分钟';
			}
		}

		//待发订单
		$staysend = DB::table('cs_order')->where('order_status',3)->count('*');
		//待审核代理商
		$stayagent =DB::table('cs_agent')->where('agent_status',2)->count('*');
		//图表数据

		$res = DB::table('cs_order')->select(DB::raw("date_format(from_unixtime(created_at),'%Y-%m-%d') as tis"),DB::raw('count(*) as num'))
		->groupBy('tis')->get()->toArray();
		$begintime = $lastweek;$endtime = $today;
		for ($start = $begintime; $start <= $endtime; $start += 24 * 3600) {
			$arr[]=date("Y-m-d", $start);
		}
		$tab=array();
		if (empty($res)) {
			foreach ($arr as $key => $value) {
					$tab[$key]['num'] =0;
					$tab[$key]['time'] = $value; 
			}
		}else{

			foreach ($arr as $k => $v) {
				foreach ($res as $key => $value) {
					if ($value['tis']!=$v) {
						$tab[$k]['num'] =0;

						$tab[$k]['time']=$v;
						continue;
					}else{
						$tab[$k]['num'] = $value['num'];

						$tab[$k]['time']=$v;
						break;
					}
				}
			}
		}
		//登录信息
		$admin_id = session('admin_id');
		$land = DB::table('sys_admin')->select('current_login_ip','last_login_ip','current_login_time','last_login_time')->where('id',$admin_id)->first();

		$data=array(
			'todaymoney'=>$todaymoney,
			'total'=>$total,
			'early'=>$count,
			'yestersale'=>$yestersale,
			'yesmoney'=>$yesmoney,
			'lastweeksale'=>$lastweeksale,
			'lastweekMoney'=>$lastweekMoney,
			'orderlist'=>$orderlist,
			'staysend'=>$staysend,
			'stayagent'=>$stayagent,
			'tab'=>$tab,
			'land'=>$land

		);
		
		
		return apiReturn(0,'ok',$data);

    }

}
