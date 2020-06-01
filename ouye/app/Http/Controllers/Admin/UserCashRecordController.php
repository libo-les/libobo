<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;  
use App\Http\Models\UserCashRecord;
use Excel;

/**
 * 提现记录
 */
class UserCashRecordController extends BaseController
{	
	/**
	 * [index 提现记录列表]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
    public function index(Request $request)
    {	
    	$perPage = 10;
    	$columns = ['*'];
    	$pageName = 'page';
    	$currentPage = $request->input('page');
    	$name =$request->input('name');
    	$status =$request->input('status');
    	$time = $request->input('ask_for_date');
    	$star=strtotime($time);
    	$end = $star+(24*3600);
		if (!empty($name)&&!empty($status)&&!empty($time)) {
			
			$res = UserCashRecord::join('cs_user','cs_user.id','=','cs_user_cash_record.uid')
			->where('cs_user.nickname','like','%'.$name.'%')
			->orwhere('cs_user_cash_record.realname','like','%'.$name.'%')
			->where('cs_user_cash_record.status',$status)
			->whereBetween('cs_user_cash_record.ask_for_date',[$star,$end])
			->paginate($perPage, $columns, $pageName, $currentPage)->toArray();

		}elseif (empty($name)&&!empty($status)&&!empty($time)) {

			$res = UserCashRecord::join('cs_user','cs_user.id','=','cs_user_cash_record.uid')
			->where('cs_user_cash_record.status',$status)
			->whereBetween('cs_user_cash_record.ask_for_date',[$star,$end])
			->paginate($perPage, $columns, $pageName, $currentPage)->toArray();

		}elseif (!empty($name)&&empty($status)&&!empty($time)) {
				
			$res = UserCashRecord::join('cs_user','cs_user.id','=','cs_user_cash_record.uid')
			->where('cs_user.nickname','like','%'.$name.'%')
			->orwhere('cs_user_cash_record.realname','like','%'.$name.'%')
			->whereBetween('cs_user_cash_record.ask_for_date',[$star,$end])
			->paginate($perPage, $columns, $pageName, $currentPage)->toArray();

		}elseif (!empty($name)&&!empty($status)&&empty($time)) {

			$res = UserCashRecord::join('cs_user','cs_user.id','=','cs_user_cash_record.uid')
			->where('cs_user.nickname','like','%'.$name.'%')
			->orwhere('cs_user_cash_record.realname','like','%'.$name.'%')
			->where('cs_user_cash_record.status',$status)
			->paginate($perPage, $columns, $pageName, $currentPage)->toArray();	

		}elseif (!empty($name)&&empty($status)&&empty($time)) {
			
			$res = UserCashRecord::join('cs_user','cs_user.id','=','cs_user_cash_record.uid')
			->where('cs_user.nickname','like','%'.$name.'%')
			->orwhere('cs_user_cash_record.realname','like','%'.$name.'%')
			->paginate($perPage, $columns, $pageName, $currentPage)->toArray();

		}elseif (empty($name)&&!empty($status)&&empty($time)) {
			
			$res = UserCashRecord::join('cs_user','cs_user.id','=','cs_user_cash_record.uid')
			->where('cs_user_cash_record.status',$status)
			->paginate($perPage, $columns, $pageName, $currentPage)->toArray();

		}elseif(empty($name)&&empty($status)&&!empty($time)){

			$res = UserCashRecord::join('cs_user','cs_user.id','=','cs_user_cash_record.uid')
			->whereBetween('cs_user_cash_record.ask_for_date',[$star,$end])
			->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
		}else{
			$res = UserCashRecord::join('cs_user','cs_user.id','=','cs_user_cash_record.uid')
			->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
		}
    	if (!empty($res)) {
    		return apiReturn(0,'ok',$res);
    	}else{
    		return apiReturn(1,'暂时无数据');
    	}
    	
    }

   

}
