<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;  
use App\Http\Models\Position;
/**
 * 广告位管理
 */
class PositionController extends BaseController
{	
	/**
	 * [index 广告位列表]
	 * @param  string $value [description]
	 * @return [type]        [json]
	 */
    public function index(Request $request)
    {   
        $perPage = 10;
        $columns = ['*'];
        $pageName = 'page';
        $currentPage = $request->input('page');
    	$res = Position::paginate($perPage, $columns, $pageName, $currentPage)->toArray();
    	return  apiReturn(0,'ok',$res);
    }
    /**
     * [addposition 添加广告位]
     * @param  Request $requset [description]
     * @return [type]           [json]
     */
    public function addposition(Request $request)
    {
    	$data = $request->all();
        $data['created_at'] = time();
        $data['updated_at'] = time();

    	$res=Position::insert($data);
    	if ($res==1) {
    		return apiReturn(0,'操作成功');
    	}else{
    		return apiReturn(1,'操作失败');
    	}
    }
    /**
     * [updposition 修改广告位信息]
     * @param  Request $requset [description]
     * @return [type]           [json]
     */
    public function updposition(Request $request)
    {
    	$data = $request->all();
    	$res = position::where('id',$data['id'])->update($data);
    	if ($res==1) {
    		return apiReturn(0,'操作成功');
    	}else{
    		return apiReturn(1,'操作失败');
    	}
    }
    /**
     * [delposition 删除广告位]
     * @param  Request $requset [description]
     * @return [type]           [json]
     */
    public function delposition(Request $request)
    {
    	$id = $request->input('id');
    	$res = position::where('id',$id)->delete();
    	if ($res==1) {
    		return apiReturn(0,'操作成功');
    	}else{
    		return apiReturn(1,'操作失败');
    	}
    }
    /**
     * [positionDetails 广告位详情]
     * @param  Request $request [description]
     * @return [type]           [json]
     */
    public function positionDetails(Request $request)
    {
        $id = $request->input('id');
        $res = position::where('id',$id)->get()->toArray();
        return apiReturn(0,'广告位详情',$res);
    }
    /**
     * [positionDown 广告位下拉表]
     * @return [type] [description]
     */
    public function positionDown()
    {
        $res = position::select('id','ap_name','type')->get()->toArray();
        return apiReturn(0,'广告位下拉表',$res);
    }

}
