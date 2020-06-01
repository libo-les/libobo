<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\Http\Models\Config;
use App\Http\Models\OrderShopReturn;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Facades\Image;

class SetupController extends BaseController
{
    /**
     * [delivery 店铺信息设置]
     * @return [type] [description]
     */
    public function delivery()
    {
    	$fee=config::where('key','deliver_fee')->pluck('jvalue')->first();
    	$store_head=config::where('key','store_head')->pluck('value')->first();
    	$store_name=config::where('key','store_name')->pluck('value')->first();
      $each_box = config::where('key','each_box')->pluck('value')->first();
      $goods_warn = config::where('key','goods_warn')->value('value');
      $bonus_scale = config::where('key','bonus_scale')->value('value');
      $sell_award_scale = config::where('key','sell_award_scale')->value('value');
      $spokesman_award =config::where('key','expand_spokesman_award')->value('value');
      $agent_award =config::where('key','expand_agent_award')->value('value');
    	$arr = DB::table('cs_order_shop_return')->first();
    	$data=array(
        'fee'=>$fee,
        'store_head'=>config('app.img_url').'/'.$store_head,
        'store_name'=>$store_name,
        'shop_address'=>$arr['shop_address'],
        'seller_name'=>$arr['seller_name'],
        'seller_mobile'=>$arr['seller_mobile'],
        'seller_zipcode'=>$arr['seller_zipcode'],
        'each_box'=>$each_box,
        'goods_warn'=>$goods_warn,
        'bonus_scale'=>$bonus_scale,
        'sell_award_scale'=>$sell_award_scale,
        'spokesman_award'=>$spokesman_award,
        'agent_award'=>$agent_award
      );
    	return apiReturn(0,'ok',$data);
    	
    }
    /**
     * [updStore 修改店铺信息]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
   	public function updStore(Request $request)
   	{
   		$data = $request->all();
      $returnmas = OrderShopReturn::get()->toArray();
      if (empty($returnmas)) {
        $returngoods = OrderShopReturn::insert(['shop_address'=>$data['shop_address'],'seller_name'=>$data['seller_name'],'seller_mobile'=>$data['seller_mobile'],'seller_zipcode'=>$data['seller_zipcode']]);
      }else{
        //修改退货信息
      $returngoods = OrderShopReturn::where('id',0)->update(['shop_address'=>$data['shop_address'],'seller_name'=>$data['seller_name'],'seller_mobile'=>$data['seller_mobile'],'seller_zipcode'=>$data['seller_zipcode']]);
      }
   		//修改配送邮费
   		$fee=config::where('key','deliver_fee')->pluck('jvalue')->first();
   		$fee['over']['fee'] = $data['fee'];
   		$fee['over']['money'] = $data['money'];
      $fee['basic_fee'] =$data['basic_fee'];
   		$fee = json_encode($fee);
   		$updfee = Config::where('key','deliver_fee')->update(['jvalue'=>$fee]);
   		//修改店铺名
   		$updname = config::where('key','store_name')->update(['value'=>$data['store_name']]);
      //修改每箱数量
      $each_box = config::where('key','each_box')->update(['value'=>$data['each_box']]);
      //修改仓储分红
      $bonus_scale =config::where('key','bonus_scale')->update(['value'=>$data['bonus_scale']]);
      //出售商品奖励比例
      $sell_award_scale =config::where('key','sell_award_scale')->update(['value'=>$data['sell_award_scale']]);
      //修改商品预警值
      $goods_warn=config::where('key','goods_warn')->update(['value'=>$data['goods_warn']]);
      //修改发展代言人奖励
      $spokesman_award=config::where('key','expand_spokesman_award')->update(['value'=>$data['spokesman_award']]);
      //修改发展代理商奖励
      $agent_award=config::where('key','expand_agent_award')->update(['value'=>$data['agent_award']]);
   		//修改店铺头像    
      $type=func_is_base64($data['store_head']);
      if ($type==true) {
        $file = Image::make($data['store_head']);
        $size=$file->width();
        $name = time().'.png';
        if (!file_exists("storage/uploads/".date('Ymd'))) {
          mkdir("storage/uploads/".date('Ymd'));
        }
        if ($size>150) {
          $file->fit(150, 150);
          $file->save("storage/uploads/".date('Ymd').'/'.$name);
          $shophead=$file->basePath();
        }else{
          $file->save("storage/uploads/".date('Ymd').'/'.$name);
          $shophead=$file->basePath();
        }

      }else{
        $shophead = substr($data['store_head'],strpos($data['store_head'],"storage")); 
      }
   		
   		$updhead = Config::where('key','store_head')->update(['value'=>$shophead]);
   		if ($returngoods==1&&$updfee==1&&$updname==1&&$updhead==1) {
   			return apiReturn(0,'操作成功');
   		}elseif ($returngoods!=1&&$updfee==1&&$updname==1&&$updhead==1) {
   			return apiReturn(1,'退货信息修改失败');
   		}elseif ($returngoods==1&&$updfee!=1&&$updname==1&&$updhead==1) {
   			return apiReturn(1,'配送费信息修改失败');
   		}elseif ($returngoods==1&&$updfee==1&&$updname!=1&&$updhead==1) {
   			return apiReturn(1,'店铺名称修改失败');
   		}else{
   			return apiReturn(1,'店铺头像修改失败');
   		}


   	}
    

	/**
	 * [SystemInfo 系统信息]
	 */
	public function SystemInfo()
	{	
		$res = DB::select('select version() as version');

		$data = array(
			'operatingsystem'=>php_uname(), 
			'ambient' =>$_SERVER['SERVER_SOFTWARE'],
			'mysql' =>$res[0]['version'],
			'max_filesize'=>ini_get('upload_max_filesize'),
			'max_execution_time'=>ini_get("max_execution_time") . 's',
			'Zlib'=>function_exists('gzclose') ? 'YES' : 'NO',
			'DnsIp'=>$_SERVER['SERVER_NAME'].'/'.$_SERVER['SERVER_ADDR'],
			'PHP_VERSION'=>PHP_VERSION,
			'GD'=>gd_info()['GD Version'],
			'Memory'=>get_cfg_var ("memory_limit")?get_cfg_var("memory_limit"):"无",
			'safe_mode'=>(boolean)ini_get('safe_mode') ? 'YES' : 'NO',
			'curl'=>function_exists('curl_init') ? 'YES' : 'NO'
			);
   			return apiReturn(0,'系统信息',$data); 	
		
	}

}
