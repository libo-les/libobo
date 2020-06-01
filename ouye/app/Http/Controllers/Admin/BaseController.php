<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Models\Module;
use Intervention\Image\ImageManager;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\DB;

class BaseController extends Controller
{	 
	
	public function __construct()
	{		
		$this->request = request();
    // 验证是否登录
		$this->middleware(function ($request, $next) {
			if (empty(session('admin_id'))) {
				exit(json_encode(array('errcode' => 10004, 'errmsg' => '没有登录')));
			}
			$this->check_priv($this->request,$next);
			return $next($request);
		});
		
	}

	public function check_priv(Request $request)
	{	
		$res = $request->route()->getActionName('controller');
		$aa = strrchr($res,'\\');
		$str = ltrim($aa,'\\');
		
		$act_list =session('act_lists');
		if ($act_list=='all'||$str=='BaseController@Futext') {
			return true;
		}else{
			$act_list = explode(',', $act_list);
			$list = module::whereIn('id',$act_list)->select('module_name','controller','method','pid','id','is_menu')->get()->toArray();
			$tab = array();
			foreach ($list as $key => $value) {
				$tab[]=$value['controller'].'@'.$value['method'];
			}
			$tab = array_values(array_unique($tab));
			
			if (!in_array($str, $tab)) {
				exit(json_encode(array('errcode' => 10005, 'errmsg' => '没有[' . ($str) . ']权限')));
			}
		}
	}

	/**
	 * [Futext 副文本编辑器图片处理]
	 * @param Request $request [description]
	 */
	public function Futext(Request $request)
	{	
		$res=$request->file('file');
		$name = time().rand(1000,9999).'.png';
		$file = Image::make($res);
		if (!file_exists("storage/uploads/".date('Ymd'))) {
			mkdir("storage/uploads/".date('Ymd'));
		}
		$file->save("storage/uploads/".date('Ymd').'/'.$name);
		$path = $file->basePath();
		return apiReturn(0,'ok',$path);	
	}
	

}
