<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;  
use App\Http\Models\AlbumPicture;

class PictureController extends Controller
{
	public function index()
	{
		$res = AlbumPicture::select('id','pic_cover_big')->get()->toArray();
		$serve = config('app.img_url').'/';
		foreach ($res as $key => $value) {
			$res[$key]['img_path'] = $serve.$value['pic_cover_big'];
			
		}
		return apiReturn(0,'图片',$res);
	}

	public function delpicture(Request $request)
	{
		$id = $request->input('id');
		$res = AlbumPicture::where('id',$id)->delete();
		if ($res==1) {
			return apiReturn(0,'删除成功');
		}else{
			return apiReturn(1,'删除失败');
		}
	}


}
