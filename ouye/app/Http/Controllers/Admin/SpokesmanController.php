<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;  
use App\Http\Models\UserSpokesman;
use Illuminate\Support\Facades\DB;
use Excel;
/**
 * 代言人管理
 */
class SpokesmanController extends BaseController
{	
	/**
	 * [index 代言人列表]
	 * @return [type] [json]
	 */
   	public function index(Request $request)
   	{
      $name = $request->input('name');
      $perPage = 10;
      $columns = ['*'];
      $pageName = 'page';
      $currentPage = $request->input('page');
      if (!empty($name)) {
          $res = UserSpokesman::join('cs_user','cs_user.id','=','cs_user_spokesman.uid')
         ->select('nickname','real_name','avatar','city','province','country','telephone','balance')
         ->where('nickname','like','%'.$name.'%')
         ->orWhere('real_name','like','%'.$name.'%')
         ->where('spokesman_status',4)->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
      }else{
        $res = UserSpokesman::join('cs_user','cs_user.id','=','cs_user_spokesman.uid')
         ->select('nickname','real_name','avatar','city','province','country','telephone','balance')
         ->where('spokesman_status',4)->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
      }
   		
   		return apiReturn(0,'ok',$res);
   	}
   	/**
   	 * [stayexamine 待审核代言人]
   	 * @return [type] [description]
   	 */
   	public function stayexamine()
   	{
   		$res = UserSpokesman::join('cs_user','cs_user.id','=','cs_user_spokesman.uid')
         ->select('nickname','real_name','avatar','city','province','country','telephone','spokesman_status')
         ->where('spokesman_status',3)->get()->toArray();
   		return apiReturn(0,'ok',$res);
   	}
      
   	/**
   	 * [examine 审核代言人]
   	 * @param  Request $request [description]
   	 * @return [type]           [json]
   	 */
   	public function examine(Request $request)
   	{
   		$id = $request->input('id');
   		$res = UserSpokesman::where('uid',$id)->update(['spokesman_status'=>4]);
   		if ($res==1) {
   			return apiReturn(0,'操作成功',$res);
   		}else{
   			return apiReturn(1,'操作失败');
   		}

   	}
      /**
       * [stock 用户库存]
       * @return [type] [json]
       */
      public function stock()
      {
        $res = DB::table('cs_user as a')
        ->join('cs_user_stock as b','b.uid','=','a.id')
        ->join('cs_goods as c','b.goods_id','=','c.id')
        ->select('a.nickname','c.goods_name','b.stock_num','b.accumulate_num')
        ->get()->toArray();
       return apiReturn(0,'ok',$res);
      }
      /**
       * [stockrecord 用户库存记录]
       * @return [type] [description]
       */
      public function stockrecord()
      {
         $res = DB::table('cs_user_stock_record as a')
         ->join('cs_user as b','a.uid','=','b.id')
         ->join('cs_user_stock as c','a.stock_id','=','c,id')
         ->join('cs_goods as d','a.goods_id','=','d.id')
         ->get()->toArray();
          return apiReturn(0,'ok',$res);
      }

      public function spokExport()
      {
           $name = iconv('UTF-8', 'GBK', '代言人表');
           Excel::create($name,function($excel){

            $excel->sheet('score', function($sheet){

             $data = UserSpokesman::join('cs_user','cs_user.id','=','cs_user_spokesman.uid')
             ->select('nickname','real_name','avatar','city','province','country','telephone','balance')
             ->where('spokesman_status',4)->get()->toArray();
             $sheet->appendRow(['用户昵称','真实姓名','头像','所在城市','手机号','可提现金额']);
                 foreach ($data as $key => $value) {
                    $sheet->appendRow([
                      $value['nickname'],
                      $value['real_name'],
                      $value['avatar'],
                      $value['country']. $value['province'].'省'.$value['city'].'市',
                      $value['telephone'],
                      $value['balance']
                    ]);
              }     

            });

          })->export('xls');
        




      }



}
