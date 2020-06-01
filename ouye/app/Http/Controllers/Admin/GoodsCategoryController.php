<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;  
use App\Http\Models\GoodsCategory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * 商品分类
 */
class GoodsCategoryController extends BaseController
{	
	/**
	 * [index 商品分类列表]
	 * @return [type] [json]
	 */
	public function index(Request $request)
	{	
		$perPage = 10;
		$columns = ['*'];
		$pageName = 'page';
		$currentPage = $request->input('page');
		$res = GoodsCategory::orderBy('sort','desc')->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
		return apiReturn(0,'ok',$res);
	}
	/**
	 * [addcategory 添加商品分类]
	 * @param  Request $request [description]
	 * @return [type]           [json]
	 */
	public function addcategory(Request $request)
	{	

		$data = $request->all();
		// $data['category_pic'] = $request->file('category_pic')->store('public/category');
		$data['created_at'] =time();
		$data['updated_at'] =time();
		$res = GoodsCategory::insert($data);
		if ($res==1) {
			return apiReturn(0,'操作成功！');
		}else{
			return apiReturn(1,'操作失败,请全部填写！');
		}
	}
	/**
	 * [updcategory 修改商品分类]
	 * @param  Request $request [description]
	 * @return [type]           [json]
	 */
	public function updcategory(Request $request)
	{
		$data = $request->all();
		// $data['category_pic'] = $request->file('category_pic')->store('public/category');
		$res = GoodsCategory::where('id',$data['id'])->update($data);
		if ($res==1) {
			return apiReturn(0,'操作成功！');
		}else{
			return apiReturn(1,'操作失败！');
		}
	}
	/**
	 * [delcategory 删除商品分类]
	 * @param  Request $request [description]
	 * @return [type]           [json]
	 */
	public function delcategory(Request $request)
	{
		$id = $request->input('id');
		$ids = explode(',',$id);
		foreach ($ids as $key => $value) {
			$res = GoodsCategory::where('id',$value)->delete();		
		}
		return apiReturn(0,'操作成功！');
		
	}
	/**
	 * [categorydetails 商品分类详情]
	 * @param  Request $request [description]
	 * @return [type]           [json]
	 */
	public function details(Request $request)
	{
		$id = $request->input('id');
		$res = GoodsCategory::where('id',$id)->get()->toArray();
		return apiReturn(0,'分类详情',$res);	
	}
	/**
	 * [downcategory 下拉框选分类]
	 * @return [type] [description]
	 */
	public function downcategory()
	{
		$res = GoodsCategory::get()->toArray();
		return apiReturn(0,'',$res);
	}

}
