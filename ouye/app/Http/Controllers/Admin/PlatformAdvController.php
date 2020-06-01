<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use App\Http\Models\PlatformAdv;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Facades\Image;
/**
 * 广告管理
 */
class PlatformAdvController extends BaseController
{
    /**
     * [index 广告列表]
     * @return [type] [description]
     */
    public function index(Request $request)
    {
    	$perPage = 10;
    	$columns = ['*'];
    	$pageName = 'page';
    	$currentPage = $request->input('page');
    	$name = $request->input('adv_title');
    	if (empty($name)) {
    		$res = DB::table('cs_platform_adv')->join('cs_platform_adv_position','cs_platform_adv.ap_id','=','cs_platform_adv_position.id')
    		->select('ap_name','adv_title','adv_image','sort','cs_platform_adv.is_use','cs_platform_adv.id')
    		->orderBy('sort','desc')
    		->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
    	}else{
    		$res = DB::table('cs_platform_adv')->join('cs_platform_adv_position','cs_platform_adv.ap_id','=','cs_platform_adv_position.id')
    		->select('ap_name','adv_title','adv_image','sort','cs_platform_adv.is_use','cs_platform_adv.id')
    		->where('adv_title','like','%'.$name.'%')
    		->orderBy('sort','desc')
    		->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
    	}
    	for ($i=0; $i <=count($res['data'])-1 ; $i++) { 
			$res['data'][$i]['adv_image'] = config('app.img_url').'/'.$res['data'][$i]['adv_image'];
			
		}
    	return apiReturn(0,'ok',$res);
    }

	/**
	 * [addplat 添加广告]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function addplat(Request $request)
	{
		$data = $request->all();
		if($data['url_id']==7){
			$data['url_id']=0;
		}
		if (!empty($data['adv_image'])) {
			$file = Image::make($data['adv_image']);
			$size=$file->width();
			$name = time().'.png';
			if ($size>650) {
				$file->fit(650, 260);
				$picture = $file->save("storage/uploads/$name");
				$path = $file->basePath();
			}else{
				$picture = $file->save("storage/uploads/$name");
				$path =$file->basePath();
			}
			$data['adv_image'] =$path;
			$data['created_at'] = time();
			$data['updated_at'] = time();
			$res = PlatformAdv::insert($data);
		}else{
			$data['created_at'] = time();
			$data['updated_at'] = time();
			$res = PlatformAdv::insert($data);
		}

		if ($res==1) {
			return apiReturn(0,'ok',$res);
		}else{
			return apiReturn(1,'no');
		}
	}
	/**
	 * [updflat 修改广告信息]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function updflat(Request $request)
	{
		$data = $request->all();
		if($data['url_id']==7){
			$data['url_id']=0;
		}elseif($data['url_id']==0){
			$data['url_param']='';
		}
		$str =substr($data['adv_image'],0,4);
		if ($str=='data') {
			$file = Image::make($data['adv_image']);
			$size=$file->width();
			$name = time().'.png';
			if ($size>650) {
				$file->fit(650, 260);
				$picture = $file->save("storage/uploads/$name");
				$path = $file->basePath();
			}else{
				$picture = $file->save("storage/uploads/$name");
				$path =$file->basePath();
			}
			$data['adv_image'] =$path;
			$res = PlatformAdv::where('id',$data['id'])->update($data);
		}else{

			$data['adv_image'] = strstr($data['adv_image'],'storage',false);
			$res = PlatformAdv::where('id',$data['id'])->update($data);			
		}

		if ($res==1) {
			return apiReturn(0,'ok',$res);
		}else{
			return apiReturn(1,'no');
		}
	}

	/**
	 * [delfalt 删除广告]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function delfalt(Request $request)
	{
		$di = $request->input('id');
		$res = PlatformAdv::where('id',$di)->delete();
		if ($res==1) {
			return apiReturn(0,'ok',$res);
		}else{
			return apiReturn(1,'no');
		}
	}
	/**
	 * [faltDetail 广告信息详情]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function faltDetail(Request $request)
	{
		$id = $request->input('id');
		$data = DB::table('cs_platform_adv')->where('id',$id)->first();
		if($data['url_id']==0){
			$data['adv_image']=config('app.img_url').'/'.$data['adv_image'];
			return apiReturn(0,'广告信息详情',$data);	
		}
		$res = DB::table('cs_platform_adv')
		->join('cs_platform_adv_url','cs_platform_adv.url_id','=','cs_platform_adv_url.id')
		->where('cs_platform_adv.id',$id)
		->first();
		
		$res['adv_image'] = config('app.img_url').'/'.$res['adv_image'];
		if ($res['set_type']==3) {
			$arr = DB::table('cs_goods')->select('goods_name')->where('id',$res['url_param'])->first();
			$res['url_name'] = $arr['goods_name'];
		}elseif ($res['set_type']==5) {
			$arr = DB::table('cs_task')->select('title')->where('id',$res['url_param'])->first();
			$res['url_name'] = $arr['title'];
		}else{
			return apiReturn(0,'广告信息详情',$res);	
		}		
		return apiReturn(0,'广告信息详情',$res);	
	}
	/**
	 * [faltgoods 商品列表弹窗]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function faltGoods(Request $request)
	{
		$perPage = 6;
		$columns = ['*'];
		$pageName = 'page';
		$currentPage = $request->input('page');
		$res = DB::table('cs_goods')->join('sys_album_picture','sys_album_picture.id','=','cs_goods.picture')
		->select('cs_goods.id','cs_goods.goods_name','sys_album_picture.pic_cover_small','cs_goods.stock','cs_goods.created_at','cs_goods.promotion_price','cs_goods.min_stock_alarm')
		->orderBy('cs_goods.id','desc')
		->paginate($perPage, $columns, $pageName, $currentPage)->toArray();

		for ($i=0; $i <=count($res['data'])-1 ; $i++) { 
			$res['data'][$i]['img'] = config('app.img_url').'/'.$res['data'][$i]['pic_cover_small'];
			$res['data'][$i]['title'] = $res['data'][$i]['goods_name'];
			unset($res['data'][$i]['pic_cover_small']);
			unset($res['data'][$i]['goods_name']);

		}
		if (count($res)==0) {
			return apiReturn(1,'数据为空');
		}else{
			return apiReturn(0,'ok',$res);
		}	
	}
	/**
	 * [faltTask 任务列表弹窗]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function faltTask(Request $request)
	{
		$perPage = 6;
		$columns = ['*'];
		$pageName = 'page';
		$currentPage = $request->input('page');
		$res = DB::table('cs_task')->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
		foreach ($res['data'] as $key => $value) {
				$res['data'][$key]['img'] = config('app.img_url').'/'.$value['img'];
				$res['data'][$key]['small_img'] = config('app.img_url').'/'.$value['small_img'];
		}

		return apiReturn(0,'ok',$res);
	}

	/**
	 * [setSort 设置排序]
	 * @param Request $request [description]
	 */
	public function setSort(Request $request)
	{
		$id = $request->input('id');
		$sort = $request->input('sort');
		$use = $request->input('is_use');
		$res = PlatformAdv::where('id',$id)->update(['sort'=>$sort,'is_use'=>$use]);
		if ($res==1) {
			return apiReturn(0,'操作成功');
		}else{
			return apiReturn(1,'操作失败');
		}	
	}

}

