<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use App\Http\Models\PlatformAdvUrl;
use Illuminate\Support\Facades\DB;

class PlatfotmAdvUrlController extends BaseController
{	
	/**
	 * [index 广告链接列表]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
    public function index(Request $request)
    {	
    	$perPage = 10;
        $columns = ['*'];
        $pageName = 'page';
        $currentPage = $request->input('page');
    	$res = PlatformAdvUrl::paginate($perPage, $columns, $pageName, $currentPage)->toArray();
    	return apiReturn(0,'广告链接列表',$res);
    }

    /**
     * [addPlatLink 添加广告链接]
     * @param Request $request [description]
     */
    public function addPlatLink(Request $request)
    {
    	$data= $request->all();
    	$data['created_at']= time();
    	$data['updated_at']= time();
    	$res = PlatformAdvUrl::insert($data);
    	if($res==1){
    		return apiReturn(0,'添加成功');
    	}else{
    		return apiReturn(1,'添加失败');
    	}
    }

    /**
     * [delPlatLink 删除广告链接]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function delPlatLink(Request $request)
    {
    	$id = $request->input('id');
    	$res = PlatformAdvUrl::where('id',$id)->delete();
    	if($res==1){
    		return apiReturn(0,'删除成功');
    	}else{
    		return apiReturn(1,'删除失败');
    	}
    }

    /**
     * [platLinkDetail 广告链接详情]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function platLinkDetail(Request $request)
    {
    	$id = $request->input('id');
    	$res = PlatformAdvUrl::where('id',$id)->first()->toArray();
    	return apiReturn(0,'广告链接详情',$res);
    }

    /**
     * [updPlatLink 修改广告链接]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function updPlatLink(Request $request)
    {
    	$data = $request->all();
    	$res = PlatformAdvUrl::where('id',$data['id'])->update($data);
    	if ($res==1) {
    		return apiReturn(0,'修改成功');
    	}else{
    		return apiReturn(1,'修改失败');
    	}
    }

    /**
     * [platLinkDown 广告链接下拉列表]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function platLinkDown(Request $request)
    {
    	$id = $request->input('id');
        $pos = DB::table('cs_platform_adv_position')->where('id',$id)->value('type');
    	$res = PlatformAdvUrl::where('platform_type',$pos)->get()->toArray();
    	return apiReturn(0,'广告链接下拉列表',$res);
    }


}