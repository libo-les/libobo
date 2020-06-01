<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use App\Http\Models\Goods;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManager;
use Intervention\Image\Facades\Image;
/**
 * 商品
 */
class GoodsController extends BaseController
{
	/**
	 * [index 商品列表，商品搜索]
	 * @param  Request $request [description]
	 * @param [category_id]  [商品分类Id]
	 * @param [goods_name] [商品名称]
	 * @return [type]           [json]
	 */
	public function index(Request $request)
	{
		$perPage = 10;
		$columns = ['*'];
		$pageName = 'page';
		$currentPage = $request->input('page');
		$category_id = $request->input('category_id');
		$goods_name = $request->input('goods_name');
		$state = $request->input('state');
		$key = $request->input('key');
		$type = $request->input('type');
		$serve = config('app.img_url').'/';//获取当前域名  
		if (!empty($key)) {
			
			if (!empty($category_id)&&!empty($goods_name)&&isset($state)==true) {

				$res = Goods::join('sys_album_picture','sys_album_picture.id','=','cs_goods.picture')
				->select('cs_goods.id','cs_goods.goods_name','sys_album_picture.pic_cover_big','cs_goods.stock','cs_goods.created_at','cs_goods.promotion_price','cs_goods.min_stock_alarm','cs_goods.state','cs_goods.sort')
				->where('goods_name','like','%'.$goods_name.'%')
				->where('category_id',$category_id)
				->where('state',$state)
				// ->orderBy('cs_goods.sort','desc')
				->orderBy($key,$type)
				->paginate($perPage, $columns, $pageName, $currentPage)->toArray();

			}elseif (empty($category_id)&&!empty($goods_name)&&isset($state)==true) {

				$res = Goods::join('sys_album_picture','sys_album_picture.id','=','cs_goods.picture')
				->select('cs_goods.id','cs_goods.goods_name','sys_album_picture.pic_cover_big','cs_goods.stock','cs_goods.created_at','cs_goods.promotion_price','cs_goods.min_stock_alarm','cs_goods.state','cs_goods.sort')
				->where('goods_name','like','%'.$goods_name.'%')
				->where('state',$state)
				// ->orderBy('cs_goods.sort','desc')
				->orderBy($key,$type)
				->paginate($perPage, $columns, $pageName, $currentPage)->toArray();

			}elseif (!empty($category_id)&&empty($goods_name)&&isset($state)==true) {

				$res = Goods::join('sys_album_picture','sys_album_picture.id','=','cs_goods.picture')
				->select('cs_goods.id','cs_goods.goods_name','sys_album_picture.pic_cover_big','cs_goods.stock','cs_goods.created_at','cs_goods.promotion_price','cs_goods.min_stock_alarm','cs_goods.state','cs_goods.sort')
				->where('category_id',$category_id)
				->where('state',$state)
				// ->orderBy('cs_goods.sort','desc')
				->orderBy($key,$type)
				->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
			}elseif(!empty($category_id)&&!empty($goods_name)&&empty($state)){

				$res = Goods::join('sys_album_picture','sys_album_picture.id','=','cs_goods.picture')
				->select('cs_goods.id','cs_goods.goods_name','sys_album_picture.pic_cover_big','cs_goods.stock','cs_goods.created_at','cs_goods.promotion_price','cs_goods.min_stock_alarm','cs_goods.state','cs_goods.sort')
				->where('goods_name','like','%'.$goods_name.'%')
				->where('category_id',$category_id)
				// ->orderBy('cs_goods.sort','desc')
				->orderBy($key,$type)
				->paginate($perPage, $columns, $pageName, $currentPage)->toArray();

			}elseif (!empty($category_id)&&empty($goods_name)&&empty($state)) {
				$res = Goods::join('sys_album_picture','sys_album_picture.id','=','cs_goods.picture')
				->select('cs_goods.id','cs_goods.goods_name','sys_album_picture.pic_cover_big','cs_goods.stock','cs_goods.created_at','cs_goods.promotion_price','cs_goods.min_stock_alarm','cs_goods.state','cs_goods.sort')
				->where('category_id',$category_id)
				// ->orderBy('cs_goods.sort','desc')
				->orderBy($key,$type)
				->paginate($perPage, $columns, $pageName, $currentPage)->toArray();

			}elseif(empty($category_id)&&!empty($goods_name)&&empty($state)){
				$res = Goods::join('sys_album_picture','sys_album_picture.id','=','cs_goods.picture')
				->select('cs_goods.id','cs_goods.goods_name','sys_album_picture.pic_cover_big','cs_goods.stock','cs_goods.created_at','cs_goods.promotion_price','cs_goods.min_stock_alarm','cs_goods.state','cs_goods.sort')
				->where('goods_name','like','%'.$goods_name.'%')
				// ->orderBy('cs_goods.sort','desc')
				->orderBy($key,$type)
				->paginate($perPage, $columns, $pageName, $currentPage)->toArray();

			}elseif (empty($category_id)&&empty($goods_name)&&isset($state)==true) {
				$res = Goods::join('sys_album_picture','sys_album_picture.id','=','cs_goods.picture')
				->select('cs_goods.id','cs_goods.goods_name','sys_album_picture.pic_cover_big','cs_goods.stock','cs_goods.created_at','cs_goods.promotion_price','cs_goods.min_stock_alarm','cs_goods.state','cs_goods.sort')
				->where('state',$state)
				// ->orderBy('cs_goods.sort','desc')
				->orderBy($key,$type)
				->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
			}else{
				$res = Goods::join('sys_album_picture','sys_album_picture.id','=','cs_goods.picture')
				->select('cs_goods.id','cs_goods.goods_name','sys_album_picture.pic_cover_big','cs_goods.stock','cs_goods.created_at','cs_goods.promotion_price','cs_goods.min_stock_alarm','cs_goods.state','cs_goods.sort')
				// ->orderBy('cs_goods.sort','desc')
				->orderBy($key,$type)
				->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
			}
		}else{

			if (!empty($category_id)&&!empty($goods_name)&&isset($state)==true) {

				$res = Goods::join('sys_album_picture','sys_album_picture.id','=','cs_goods.picture')
				->select('cs_goods.id','cs_goods.goods_name','sys_album_picture.pic_cover_big','cs_goods.stock','cs_goods.created_at','cs_goods.promotion_price','cs_goods.min_stock_alarm','cs_goods.state','cs_goods.sort')
				->where('goods_name','like','%'.$goods_name.'%')
				->where('category_id',$category_id)
				->where('state',$state)
				->orderBy('cs_goods.sort','desc')
				->orderBy('cs_goods.id','desc')
				->paginate($perPage, $columns, $pageName, $currentPage)->toArray();

			}elseif (empty($category_id)&&!empty($goods_name)&&isset($state)==true) {

				$res = Goods::join('sys_album_picture','sys_album_picture.id','=','cs_goods.picture')
				->select('cs_goods.id','cs_goods.goods_name','sys_album_picture.pic_cover_big','cs_goods.stock','cs_goods.created_at','cs_goods.promotion_price','cs_goods.min_stock_alarm','cs_goods.state','cs_goods.sort')
				->where('goods_name','like','%'.$goods_name.'%')
				->where('state',$state)
				->orderBy('cs_goods.sort','desc')
				->orderBy('cs_goods.id','desc')
				->paginate($perPage, $columns, $pageName, $currentPage)->toArray();

			}elseif (!empty($category_id)&&empty($goods_name)&&isset($state)==true) {

				$res = Goods::join('sys_album_picture','sys_album_picture.id','=','cs_goods.picture')
				->select('cs_goods.id','cs_goods.goods_name','sys_album_picture.pic_cover_big','cs_goods.stock','cs_goods.created_at','cs_goods.promotion_price','cs_goods.min_stock_alarm','cs_goods.state','cs_goods.sort')
				->where('category_id',$category_id)
				->where('state',$state)
				->orderBy('cs_goods.sort','desc')
				->orderBy('cs_goods.id','desc')
				->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
			}elseif(!empty($category_id)&&!empty($goods_name)&&empty($state)){

				$res = Goods::join('sys_album_picture','sys_album_picture.id','=','cs_goods.picture')
				->select('cs_goods.id','cs_goods.goods_name','sys_album_picture.pic_cover_big','cs_goods.stock','cs_goods.created_at','cs_goods.promotion_price','cs_goods.min_stock_alarm','cs_goods.state','cs_goods.sort')
				->where('goods_name','like','%'.$goods_name.'%')
				->where('category_id',$category_id)
				->orderBy('cs_goods.sort','desc')
				->orderBy('cs_goods.id','desc')
				->paginate($perPage, $columns, $pageName, $currentPage)->toArray();

			}elseif (!empty($category_id)&&empty($goods_name)&&empty($state)) {
				$res = Goods::join('sys_album_picture','sys_album_picture.id','=','cs_goods.picture')
				->select('cs_goods.id','cs_goods.goods_name','sys_album_picture.pic_cover_big','cs_goods.stock','cs_goods.created_at','cs_goods.promotion_price','cs_goods.min_stock_alarm','cs_goods.state','cs_goods.sort')
				->where('category_id',$category_id)
				->orderBy('cs_goods.sort','desc')
				->orderBy('cs_goods.id','desc')
				->paginate($perPage, $columns, $pageName, $currentPage)->toArray();

			}elseif(empty($category_id)&&!empty($goods_name)&&empty($state)){
				$res = Goods::join('sys_album_picture','sys_album_picture.id','=','cs_goods.picture')
				->select('cs_goods.id','cs_goods.goods_name','sys_album_picture.pic_cover_big','cs_goods.stock','cs_goods.created_at','cs_goods.promotion_price','cs_goods.min_stock_alarm','cs_goods.state','cs_goods.sort')
				->where('goods_name','like','%'.$goods_name.'%')
				->orderBy('cs_goods.sort','desc')
				->orderBy('cs_goods.id','desc')
				->paginate($perPage, $columns, $pageName, $currentPage)->toArray();

			}elseif (empty($category_id)&&empty($goods_name)&&isset($state)==true) {

				$res = Goods::join('sys_album_picture','sys_album_picture.id','=','cs_goods.picture')
				->select('cs_goods.id','cs_goods.goods_name','sys_album_picture.pic_cover_big','cs_goods.stock','cs_goods.created_at','cs_goods.promotion_price','cs_goods.min_stock_alarm','cs_goods.state','cs_goods.sort')
				->where('cs_goods.state',$state)
				->orderBy('cs_goods.sort','desc')
				->orderBy('cs_goods.id','desc')
				->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
			}else{
				$res = Goods::join('sys_album_picture','sys_album_picture.id','=','cs_goods.picture')
				->select('cs_goods.id','cs_goods.goods_name','sys_album_picture.pic_cover_big','cs_goods.stock','cs_goods.created_at','cs_goods.promotion_price','cs_goods.min_stock_alarm','cs_goods.state','cs_goods.sort')
				->orderBy('cs_goods.sort','desc')
				->orderBy('cs_goods.id','desc')
				->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
			}


		}
		$warn = DB::table('sys_config')->where('key','goods_warn')->value('value');
		for ($i=0; $i <=count($res['data'])-1 ; $i++) { 
			$res['data'][$i]['image_path'] = $serve.$res['data'][$i]['pic_cover_big'];
			if ($res['data'][$i]['stock']<=$warn) {
				$res['data'][$i]['caution'] = 1;
			}else{
				$res['data'][$i]['caution'] = 0;
			}
		}
		if (count($res)==0) {
			return apiReturn(1,'数据为空');
		}else{
			return apiReturn(0,'ok',$res);
		}


	}
	/**
	 * [addgood 添加商品]
	 * @param  Request $Request [description]
	 * @return [type]           [json]
	 */
	public function addgood(Request $request)
	{
		$data = $request->all();

		if (!empty($data['img_id_array'])) {
			
			$data['img_id_array']=$this->images($data['img_id_array']);
			$data['img_id_array'] = rtrim($data['img_id_array'],',');
		}
		$rules = [
			'description'=>'required',
		];
		$message = [
			'description.required'=>'商品详情不能为空！',
		];
		$validator = Validator::make($data,$rules,$message);
		if($validator->fails()){
			return apiReturn(1,$validator->errors()->first());
		}else{
			$data['picture']=$this->image($data['picture']);
			$data['production_date'] = strtotime($data['production_date']);
			$data['created_at']=time();	
			$data['updated_at']=time();	
			
			$res = Goods::insert($data);
			if ($res==1) {
				return apiReturn(0,'添加成功');
			}else{
				return apiReturn(1,'添加失败');
			}			
			
		}

	}
	/**
	 * [isState 上下架操作]
	 * @param  Request $request [description]
	 * @return boolean          [description]
	 */
	public function isState(Request $request)
	{
		$id = $request->input('id');
		$state = $request->input('state');
		$sort = $request->input('sort');
		$res = Goods::where('id',$id)->update(['state'=>$state,'sort'=>$sort]);
		if ($res==1) {
			return apiReturn(0,'操作成功');
		}else{
			return apiReturn(1,'操作失败');
		}	
	}

	/**
	 * [updgood 修改商品]
	 * @param  Request $request [description]
	 * @return [type]           [json]
	 */
	public function updgood(Request $request)
	{
		$data = $request->all();
		$str='futu';
		if (!array_key_exists($str,$data)) {
			
			$str =substr($data['big_img_path'],0,4);
			if ($str=='data') {
				$data['picture']=$this->image($data['big_img_path']);
				unset($data['big_img_path']);
				$data['production_date'] = strtotime($data['production_date']);
				$res = Goods::where('id',$data['id'])->update($data);
			}else{
				unset($data['big_img_path']);
				$data['production_date'] = strtotime($data['production_date']);
				$res = Goods::where('id',$data['id'])->update($data);
			}		

		}else{
			
			foreach ($data['futu'] as $key => $value) {
				$sts =substr($value['url'],0,4);
				if ($sts=='data') {
					$imgid = $this->image($value['url']);
					$data['img_id_array'].=','.$imgid; 
				}else{
					unset($data['futu'][$key]);
				}
			}
			unset($data['futu']);
			$type=substr($data['big_img_path'],0,4);
			if ($type=='data') {
				$data['production_date'] = strtotime($data['production_date']);
				$data['picture']=$this->image($data['big_img_path']);
				unset($data['big_img_path']);	
				$res = Goods::where('id',$data['id'])->update($data);
			}else{
				unset($data['big_img_path']);
				$data['production_date'] = strtotime($data['production_date']);
				$res = Goods::where('id',$data['id'])->update($data);
			}


		}		
		
		if($res==1){
			return apiReturn(0,'操作成功！');
		}else{
			return apiReturn(1,'操作失败！');
		}

	}
	/**
	 * [delgood 删除商品]
	 * @param  string $value [description]
	 * @return [type]        [description]
	 */
	public function delgood(Request $request)
	{
		$id = $request->input('id');
		
		$res = Goods::where('id',$id)->delete();
		
		if($res==1){
			return apiReturn(0,'操作成功！');
		}else{
			return apiReturn(1,'操作失败！');
		}
	}

	/**
	 * [delfutu 删除副图]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function delfutu(Request $request)
	{
		$id = $request->input('id');
		
		$data = DB::table('sys_album_picture')->where('id',$id)->select('pic_cover_big','pic_cover_small')->first();
		foreach ($data as $key => $value) {
			if (file_exists ($value)==true) {
				unlink ($value);
			}else{
				continue;
			}
			
		}
		
		$res = DB::table('sys_album_picture')->where('id',$id)->delete();
		if ($res==1) {
			return apiReturn(0,'删除成功');
		}else{
			return apiReturn(1,'删除失败');
		}
		
	}




	/**
	 * [goodsDetails 商品详情]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function goodsDetails(Request $request)
	{	
		
		$serve = config('app.img_url').'/';//获取当前域名  
		$id = $request->input('id');
		$res = DB::table('cs_goods')->where('id',$id)->first();
		$imgs = DB::table('sys_album_picture')->where('id',$res['picture'])->first();
		$province = DB::table('sys_region')->where('id',$res['region'])->first();
		$brand = DB::table('cs_goods_brand')->where('id',$res['brand_id'])->first();
		$arr = explode(',', $res['img_id_array']);

		foreach ($arr as $key => $value) {

			$img_id_array = DB::table('sys_album_picture')->select('pic_cover_big','id')->where('id',$value)->first();
			if (!empty($img_id_array)) {
				$futu[$key]['url'] = $serve.$img_id_array['pic_cover_big'];
				$futu[$key]['id'] = $img_id_array['id'];
			}else{
				$futu[] ='';
			}
			
		}
		$try= array_values(array_filter($futu));
		$res['futu'] = $try;
		$res['brand'] = $brand['brand_name'];
		$res['big_img_path'] = $serve.$imgs['pic_cover_big'];
		$res['small_img_path'] =$serve.$imgs['pic_cover_small'];
		$res['province'] = $province['parent_id'];
		return apiReturn(0,'商品详情',$res);

	}



	/**
	 * [image 图片裁剪大小图]
	 * @param  Request $request [description]
	 * @return [type]           [number]
	 */
	public function image($image)
	{	
		$file = Image::make($image);
		$size=$file->width();
		$name = time().'.png';
		if (!file_exists("storage/uploads/".date('Ymd'))) {
			mkdir("storage/uploads/".date('Ymd'),0777,true);
		}
		if ($size[0]<750) {
			$names=time().rand(1000,9999).'.png';
			$picture = $file->save("storage/uploads/".date('Ymd').'/'.$names);
			$bigpath = $file->basePath();
			$bigwidth = $file->width();
			$bighign =$file->height();
			$arr = pathinfo($picture);
			$img = Image::make($image);
			$img->fit(150, 150);
			$img->save("storage/uploads/".date('Ymd').'/'.$name);
			$smallpath=$img->basePath();
			$width = $img->width();
			$hign = $img->height();
			$pic =array(
				'pic_cover_big'=>$bigpath,
				'pic_size_big'=>$bigwidth.','.$bighign,
				'pic_spec_big'=>$bigwidth.','.$bighign,
				'pic_cover_small'=>$smallpath,
				'pic_size_small'=>$width.','.$hign,
				'pic_spec_small'=>$width.','.$hign,
				'created_at'=>time(),
				'updated_at'=>time()
			);
			$inspicture = DB::table('sys_album_picture')->insert($pic);
			$max = DB::table('sys_album_picture')->max('id');
			return $max;
		}else{
			//裁剪大图
			$names=time().rand(1000,9999).'.png';
			$bigimg = Image::make($image);
			$bigimg->fit(750, 750);
			$bigimg->save("storage/uploads/".date('Ymd').'/'.$name);
			$bigpath=$bigimg->basePath();
			$bigwidth = $bigimg->width();
			$bighign = $bigimg->height();
			
			//裁剪小图
			$smallimg = Image::make($image);
			$smallimg->resize(150,150);
			$smallimg->save("storage/uploads/".date('Ymd').'/'.$names);
			$smallpath=$smallimg->basePath();
			$smallwidth = $smallimg->width();
			$smallhign = $smallimg->height();
			$pic =array(
				'pic_cover_big'=>$bigpath,
				'pic_size_big'=>$bigwidth.','.$bighign,
				'pic_spec_big'=>$bigwidth.','.$bighign,
				'pic_cover_small'=>$smallpath,
				'pic_size_small'=>$smallwidth.','.$smallhign,
				'pic_spec_small'=>$smallwidth.','.$smallhign,
				'created_at'=>time(),
				'updated_at'=>time()
			);
			$inspicture = DB::table('sys_album_picture')->insert($pic);
			$max = DB::table('sys_album_picture')->max('id');
			return $max;		
		}
	}

	/**
	 * [images 商品副图裁剪]
	 * @param  [type] $images [description]
	 * @return [type]         [description]
	 */
	public function images($images)
	{	
		$name = time().'.png';
		$str='';
		if (!file_exists("storage/uploads/".date('Ymd'))) {
			mkdir("storage/uploads/".date('Ymd'));
		}
		foreach ($images as $key => $value) {
			$file = Image::make($value);
			$size=$file->width();
			if ($size<=750) {
				$names=time().rand(1000,9999).'.png';
				$picture = $file->save("storage/uploads/".date('Ymd').'/'.$names);
				$bigpath = $file->basePath();
				$bigwidth = $file->width();
				$bighign =$file->height();
				
				$img = Image::make($value);
				$img->fit(150, 150);
				$img->save("storage/uploads/".date('Ymd').'/'.$name);
				$smallpath=$img->basePath();
				$width = $img->width();
				$hign = $img->height();

				$pic =array(
					'pic_cover_big'=>$bigpath,
					'pic_size_big'=>$bigwidth.','.$bighign,
					'pic_spec_big'=>$bigwidth.','.$bighign,
					'pic_cover_small'=>$smallpath,
					'pic_size_small'=>$width.','.$hign,
					'pic_spec_small'=>$width.','.$hign,
					'created_at'=>time(),
					'updated_at'=>time()
				);
				$inspicture = DB::table('sys_album_picture')->insert($pic);
				$max = DB::table('sys_album_picture')->max('id');
				$str.=$max.',';
				
			}else{
				$names=time().rand(1000,9999).'.png';
				$bigimg = Image::make($value);
				$bigimg->fit(750, 750);
				$bigimg->save("storage/uploads/".date('Ymd').'/'.$name);
				$bigpath=$bigimg->basePath();
				$bigwidth = $bigimg->width();
				$bighign = $bigimg->height();

			//裁剪小图
				$smallimg = Image::make($value);
				$smallimg->resize(150,150);
				$smallimg->save("storage/uploads/".date('Ymd').'/'.$names);
				$smallpath=$smallimg->basePath();
				$smallwidth = $smallimg->width();
				$smallhign = $smallimg->height();
				$pic =array(
					'pic_cover_big'=>$bigpath,
					'pic_size_big'=>$bigwidth.','.$bighign,
					'pic_spec_big'=>$bigwidth.','.$bighign,
					'pic_cover_small'=>$smallpath,
					'pic_size_small'=>$smallwidth.','.$smallhign,
					'pic_spec_small'=>$smallwidth.','.$smallhign,
					'created_at'=>time(),
					'updated_at'=>time()
				);
				$inspicture = DB::table('sys_album_picture')->insert($pic);
				$max = DB::table('sys_album_picture')->max('id');
				$str.=$max.',';
				
			}
			
		}

		return $str;
		
		
	}




}
