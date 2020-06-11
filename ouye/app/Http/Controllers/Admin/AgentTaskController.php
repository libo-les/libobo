<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use App\Http\Models\Task;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query;
use Intervention\Image\ImageManager;
use Intervention\Image\Facades\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AgentTaskController extends Controller
{
    /**
	 * [index 任务列表]
	 * @return [type] [json]
	 */
	public function index(Request $request)
	{
		$perPage = 10;
		$columns = ['*'];
		$pageName = 'page';
		$currentPage = $request->input('page');
		$res = Task::where('type',1)->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
		
		foreach ($res['data'] as $key => $value) {
				$res['data'][$key]['small_img'] =config('app.img_url').'/'.$value['small_img'];
					foreach($value['role_type'] as $k=>$v){
						if($v!=4){
							unset($res['data'][$key]);
						}
					}
		}
		$res['data'] = array_values($res['data']);
		return apiReturn(0,'ok',$res);
	}
	/**
	 * [addTask 添加任务]
	 * @param Request $request [json]
	 */
	public function addTask(Request $request)
	{
		$data=$request->all();	
		$data['role_type'] = json_encode([4]);
		$name = time().'.png';
		$image = Image::make($data['img']);
		$size=$image->width();
		if (!file_exists("storage/uploads/".date('Ymd'))) {
			mkdir("storage/uploads/".date('Ymd'));
		}
		if ($size<=750) {
			$names=time().rand(1000,9999).'.png';
			$image->save("storage/uploads/".date('Ymd').'/'.$name);
			$bigpath =$image->basePath();
			$img = Image::make($data['img']);
			$img->fit(150, 150);
			$img->save("storage/uploads/".date('Ymd').'/'.$names);
			$smallpath=$img->basePath();
		}else{
			$names=time().rand(1000,9999).'.png';
			$bigimg = Image::make($data['img']);
			$bigimg->fit(750, 750);
			$bigimg->save("storage/uploads/".date('Ymd').'/'.$name);
			$bigpath=$bigimg->basePath();

			$smallimg = Image::make($data['img']);
			$smallimg->resize(150,150);
			$smallimg->save("storage/uploads/".date('Ymd').'/'.$names);
			$smallpath=$smallimg->basePath();
		}
		$data['img'] = $bigpath;
		$data['small_img']=$smallpath;
		$data['created_at'] = time();
		$data['updated_at'] = time();
		$data['start_at']  = strtotime($data['start_at']);
		$data['end_at']  = strtotime($data['end_at']);
		$res = task::insert($data);
		if ($res==1) {
			return apiReturn(0,'操作成功！');
		}else{
			return apiReturn(1,'操作失败！');
		}
	}
	/**
	 * [updTask 修改任务]
	 * @param  Request $request [description]
	 * @return [type]           [json]
	 */
	public function updTask(Request $request)
	{
		$data=$request->all();
		$type=func_is_base64($data['img']);
		if (!file_exists("storage/uploads/".date('Ymd'))) {
			mkdir("storage/uploads/".date('Ymd'));
		}
		if ($type==true) {
			$name = time().'.png';
			$image = Image::make($data['img']);
			$size=$image->width();
			if ($size<=750) {
				$names=time().rand(1000,9999).'.png';
				$image->save("storage/uploads/".date('Ymd').'/'.$name);
				$bigpath =$image->basePath();
				$img = Image::make($data['img']);
				$img->fit(150, 150);
				$img->save("storage/uploads/".date('Ymd').'/'.$names);
				$smallpath=$img->basePath();
			}else{
				$names=time().rand(1000,9999).'.png';
				$bigimg = Image::make($data['img']);
				$bigimg->fit(750, 750);
				$bigimg->save("storage/uploads/".date('Ymd').'/'.$name);
				$bigpath=$bigimg->basePath();

				$smallimg = Image::make($data['img']);
				$smallimg->resize(150,150);
				$smallimg->save("storage/uploads/".date('Ymd').'/'.$names);
				$smallpath=$smallimg->basePath();
			}
			$data['img'] = $bigpath;
			$data['small_img']=$smallpath;
			$data['start_at']  = strtotime($data['start_at']);
			$data['end_at']  = strtotime($data['end_at']);
			$res=task::where('id',$data['id'])->update($data);
			if ($res==1) {
				return apiReturn(0,'操作成功！');
			}else{
				return apiReturn(1,'操作失败！');
			}
		}else{
			$data['img'] = substr($data['img'],strpos($data['img'],"storage")); 
			$data['start_at']  = strtotime($data['start_at']);
			$data['end_at']  = strtotime($data['end_at']);
			$res=task::where('id',$data['id'])->update($data);
			if ($res==1) {
				return apiReturn(0,'操作成功！');
			}else{
				return apiReturn(1,'操作失败！');
			}

		}

	}
	/**
	 * [delTask 删除任务]
	 * @param  Request $request [description]
	 * @return [type]           [json]
	 */
	public function delTask(Request $request)
	{
		$id=$request->input('id');

		$res = task::where('id',$id)->delete();

		if ($res==1) {
			return apiReturn(0,'操作成功！');
		}else{
			return apiReturn(1,'操作失败！');
		}
	}
	/**
	 * [taskDetails 任务详情]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function taskDetails(Request $request)
	{
		
		$id = $request->input('id');
		$res = task::where('id',$id)->first()->toArray();

		$res['img'] = config('app.img_url').'/'.$res['img'];
		$res['small_img'] = config('app.img_url').'/'.$res['small_img'];

		return apiReturn(0,'任务详情',$res);
	}
}