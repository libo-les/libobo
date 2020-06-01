<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use App\Http\Models\TaskUserCheck;
use Illuminate\Support\Facades\DB;
use App\Http\Models\UserWalletBill;
use App\Http\Models\UserAwardRecord;
use App\Http\Models\Task;
use App\Http\Models\Api\Agent;
class TaskUserCheckController extends BaseController
{
	/**
	 * [index 审核任务列表]
	 * @return [type] [description]
	 */
	public function index(Request $request)
	{
		$perPage = 10;
		$columns = ['*'];
		$pageName = 'page';
		$currentPage = $request->input('page');
		$timd = $request->input('confirm_at');
		$star=strtotime($timd);
		$end = $star+(24*3600);
		$nickname = $request->input('nickname');
		if (!empty($timd)&&!empty($nickname)) {
			$res = TaskUserCheck::join('cs_user','cs_user.id','=','cs_task_user_check.uid')
			->join('cs_task','cs_task.id','=','cs_task_user_check.task_id')
			->select('cs_task_user_check.id','cs_user.nickname','cs_task.title','cs_task_user_check.status','cs_task_user_check.created_at')
			->where('status',2)
			->whereBetween('created_at',[$star,$end])
			->where('nickname','like','%'.$nickname.'%')
			->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
		}elseif (!empty($timd)&&empty($nickname)) {
			$res = TaskUserCheck::join('cs_user','cs_user.id','=','cs_task_user_check.uid')
			->join('cs_task','cs_task.id','=','cs_task_user_check.task_id')
			->select('cs_task_user_check.id','cs_user.nickname','cs_task.title','cs_task_user_check.status','cs_task_user_check.created_at')
			->where('status',2)
			->whereBetween('created_at',[$star,$end])
			->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
		}elseif (empty($timd)&&!empty($nickname)) {
			$res = TaskUserCheck::join('cs_user','cs_user.id','=','cs_task_user_check.uid')
			->join('cs_task','cs_task.id','=','cs_task_user_check.task_id')
			->select('cs_task_user_check.id','cs_user.nickname','cs_task.title','cs_task_user_check.status','cs_task_user_check.created_at')
			->where('status',2)
			->where('nickname','like','%'.$nickname.'%')
			->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
		}else{
			$res = TaskUserCheck::join('cs_user','cs_user.id','=','cs_task_user_check.uid')
			->join('cs_task','cs_task.id','=','cs_task_user_check.task_id')
			->select('cs_task_user_check.id','cs_user.nickname','cs_task.title','cs_task_user_check.status','cs_task_user_check.created_at')
			->where('status',2)
			->paginate($perPage, $columns, $pageName, $currentPage)->toArray();

		}

		return apiReturn(0,'审核任务列表',$res);
	}
    /**
     * [taskCheckDetail 审核详情]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function taskCheckDetail(Request $request)
    {
    	$id  = $request->input('id');
    	$res = TaskUserCheck::join('cs_user','cs_user.id','=','cs_task_user_check.uid')
		->join('cs_task','cs_task.id','=','cs_task_user_check.task_id')
    	->select('cs_task_user_check.id','cs_user.nickname','cs_task.title','cs_task_user_check.status','cs_task_user_check.confirm_at','cs_task.award','cs_task_user_check.content','cs_task_user_check.images')
    	->where('cs_task_user_check.id',$id)
    	->first()->toArray();
    	if (!empty($res['images'])) {
    		foreach ($res['images'] as $key => $value) {
    			$tab[] = config('app.img_url').'/'.$value;
    		}	
    		$res['images'] = $tab;	
    	}
    	
    	return apiReturn(0,'ok',$res);
    }
    /**
     * [examineSpecTask 审核操作]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function examineSpecTask(Request $request)
    {
    	$id  = $request->input('id');
		$status = $request->input('status');
		$reason = $request->input('reason');
		
		$res = TaskUserCheck::where('id',$id)->update(['status'=>$status,'reason'=>$reason,'confirm_at'=>time(),'check_uid'=>session('admin_id')]);
		
		$task =TaskUserCheck::where('id',$id)->first()->toArray();
		$task_info = Task::where('id', $task['task_id'])->where('type', 2)->first();
		$money = $task_info->award;
		$usermas = DB::table('cs_user')->where('id',$task['uid'])->first();
    	if ($res==1) {
			if($status==3){
				// // $user = DB::table('cs_user')->where('id',$task['uid'])->update(['balance'=>$usermas['balance']+$money,'total_award'=>$usermas['total_award']+$money]);
				// $useraward = DB::table('cs_user_award_record')->insert([
				// 	'uid'=>$task['uid'],
				// 	'source'=>4,
				// 	'is_bill'=>0,
				// 	'award'=>$money,
				// ]);
				$award_record = [
					'uid' => $task['uid'],
					'source' => 8,
					'source_id' => $id,
					'source_user_id' => 0,
					'is_bill' => 0,
					'award' => $money,
				];
				$award_info = UserAwardRecord::create($award_record);
				if (in_array(4, $task_info->role_type)) {
					(new UserWalletBill)->addAwardBill($award_info);
					Agent::where('uid', $task['uid'])->increment('task_award', $task_info->award);
				}
			}
    		return apiReturn(0,'操作成功');
    	}else{
    		return apiReturn(1,'操作失败');
    	}


    }

}
