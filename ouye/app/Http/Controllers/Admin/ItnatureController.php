<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;  
use App\Http\Models\AgentGradeCondition;
use Illuminate\Support\Facades\DB;

class ItnatureController extends BaseController
{
    /**
     * [index 代理等级列表]
     * @return [type] [description]
     */
	public function index(Request $request)
	{
		$perPage = 10;
		$columns = ['*'];
		$pageName = 'page';
		$currentPage = $request->input('page');
		$res = AgentGradeCondition::paginate($perPage, $columns, $pageName, $currentPage)->toArray();
		return apiReturn(0,'ok',$res);
	}
	/**
	 * [addcondition 添加代理等级]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function addcondition(Request $request)
	{
		$data = $request->all();
		if ($data['buy_num']==0&&$data['buy_money']==0) {
			return apiReturn(1,'必须选中其中一个条件');
		}
		
		$res = AgentGradeCondition::insert($data);
		if ($res==1) {
			return apiReturn(0,'操作成功',$res);
		}else{
			return apiReturn(1,'操作失败');
		}
	}
	/**
	 * [updcondition 修改代理等级信息]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function updcondition(Request $request)
	{
		$data = $request->all();
		$res = AgentGradeCondition::where('id',$data['id'])->update($data);
		if ($res==1) {
			return apiReturn(0,'操作成功',$res);
		}else{
			return apiReturn(1,'操作失败');
		}

	}
	/**
	 * [delcondition 删除代理等级]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function delcondition(Request $request)
	{
		$id = $request->input('id');
		$res = AgentGradeCondition::where('id',$id)->delete();
		if ($res==1) {
			return apiReturn(0,'操作成功',$res);
		}else{
			return apiReturn(1,'操作失败');
		}
	}
	/**
	 * [conditionDetil 代理等级详情]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function conditionDetil(Request $request)
    {
    	$id = $request->input('id');
    	$res = AgentGradeCondition::where('id',$id)->first()->toArray();
    	return apiReturn(0,'等级详情',$res);

    }


}
