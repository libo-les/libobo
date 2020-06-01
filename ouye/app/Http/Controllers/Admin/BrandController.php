<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use App\Http\Models\Brand;

class BrandController extends BaseController
{
	/**
	 * [index 品牌列表]
	 * @return [type] [description]
	 */
	public function index(Request $request)
	{

		$perPage = 10;
		$columns = ['*'];
		$pageName = 'page';
		$currentPage = $request->input('page');
		$name = $request->input('brand_name');
		if (empty($name)) {
			$res = Brand::paginate($perPage, $columns, $pageName, $currentPage)->toArray();
		}else{
			$res = Brand::where('brand_name','like','%'.$name.'%')->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
		}

		return apiReturn(0,'商品品牌',$res);
	}
	/**
	 * [addBrand 添加品牌]
	 * @param Request $request [description]
	 */
	public function addBrand(Request $request)
	{
		$data = $request->all();
		// $data['brand_pic'] = $request->file('brand_pic')->store('public/brand');
		$data['created_at'] =time();
		$data['updated_at'] =time();
		$res = Brand::insert($data);
		if ($res==1) {
			return apiReturn(0,'操作成功！');
		}else{
			return apiReturn(1,'操作失败,请全部填写！');
		}
	}
	/**
	 * [updBrand 修改品牌]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function updBrand(Request $request)
	{
		$data = $request->all();
		if (!empty($data['brand_pic'])) {
			// $data['brand_pic'] = $request->file('brand_pic')->store('public/brand');
			$res= Brand::where('id',$data['id'])->update($data);
			if ($res==1) {
				return apiReturn(0,'操作成功！');
			}else{
				return apiReturn(1,'操作失败');
			}
		}else{
			$res= Brand::where('id',$data['id'])->update($data);
			if ($res==1) {
				return apiReturn(0,'操作成功！');
			}else{
				return apiReturn(1,'操作失败,请全部填写！');
			}
		}

	}
	/**
	 * [delBrand 删除品牌]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function delBrand(Request $request)
	{
		$id = $request->input('id');
		$res = Brand::where('id',$id)->delete();
		if ($res==1) {
			return apiReturn(0,'操作成功！');
		}else{
			return apiReturn(1,'操作失败,请全部填写！');
		}
	}
	/**
	 * [brandDetail 品牌详情]
	 * @return [type] [description]
	 */
	public function brandDetail(Request $request)
	{
		$id = $request->input('id');
		$res= Brand::where('id',$id)->get()->toArray();
		return apiReturn(0,'品牌详情',$res);

	}
	/**
	 * [dBrand 品牌下拉框]
	 * @return [type] [description]
	 */
	public function dBrand()
	{
		$res = Brand::get()->toArray();
		return apiReturn(0,'ok',$res);
	}



}
