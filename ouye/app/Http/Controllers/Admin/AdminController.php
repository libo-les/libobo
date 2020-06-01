<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Model;  
use App\Http\Models\Admin;

/**
 * 管理员管理
 */
class AdminController extends BaseController
{

	/**
	 * [searchUser 管理员列表，查询]
	 * @param  Request $request [description]
	 * @return [type]           [json]
	 */
	public function index(Request $request)
	{

		$name =  $request->input('user_name');
		if (!empty($name)) {
			$res = Db::table('sys_admin')
			->join('sys_admin_group','sys_admin_group.id','=','sys_admin.group_id')
			->select('user_name','user_tel','user_password','group_name','desc','sys_admin.id','last_login_time','group_id')
			->where('user_name','like','%'.$name.'%')
			->whereNotIn('group_id',[1])
			->paginate(10)->toArray();
			return apiReturn(0,'ok',$res);	
		}else{
			$res = Db::table('sys_admin')
			->select('user_name','user_tel','user_password','group_name','desc','sys_admin.id','last_login_time','user_status')
			->join('sys_admin_group','sys_admin_group.id','=','sys_admin.group_id')
			->whereNotIn('group_id',[1])
			->paginate(10)->toArray();
			return apiReturn(0,'ok',$res);
		}		

	}

	/**
	 * [index 添加管理员]
	 * @param  Request $request [description]
	 * @return [type]           [json]
	 */
	public function addAdmin(Request $request)
	{
		$data = $request->all();
		
		$rules = [
			'user_name'=>'required|between:4,16',
			'user_password'=>'required|between:6,20',
			'user_tel'=>'required|between:11,13',

		];
		$message = [
			'user_name.required'=>'账号不能为空！',
			'user_name.between'=>'账号必须在4-16位之间！',
			'user_password.required'=>'密码不能为空！',
			'user_password.between'=>'密码必须在6-20位之间！',
			'user_tel.required'=>'手机号不能为空！',
			'user_tel.between'=>'手机号必须在11-13位之间！',

		];
		$validator = Validator::make($data,$rules,$message);
		if($validator->fails()){
			return apiReturn(1,$validator->errors()->first());
		}else{
			$data['created_at'] = time();
			$data['updated_at'] = time();
			$data['current_login_time']=time();
			$data['user_password'] = md5($data['user_password']);
			$res = Admin::insert($data);
			if ($res==1) {
				return apiReturn(0,'添加成功！');
			}else{
				return apiReturn(1,'添加失败！');
			}

		}
		
	}

	/**
	 * [deluser 删除管理员]
	 * @param  Request $request [description]
	 * @return [type]           [json]
	 */
	public function delAdmin(Request $request)
	{	

		$id = $request->input('id');	
		$res=Admin::where('id',$id)->delete();
		if ($res==1) {
			return apiReturn(0,'操作成功！');
		}else{
			return apiReturn(1,'操作失败！');
		}
	}

	
	/**
	 * [updUser 修改管理员信息]
	 * @param  Request $request [description]
	 * @return [type]           [json]
	 */
	public function updAdmin(Request $request)
	{	

		$data =  $request->all();
		$arr = DB::table('sys_admin')->where('id',$data['id'])->first();
		if (md5($data['user_password'])!=$arr['user_password']) {
			return apiReturn(1,'密码不正确');
		}else{
			$data['user_password'] = md5($data['user_password']);
			$res = DB::table('sys_admin')->where('id',$data['id'])->update($data);
			if ($res==1) {
				return apiReturn(0,'操作成功！');
			}else{
				return apiReturn(1,'操作失败！');
			}
		}
		
		
	}
	/**
	 * [adminDetail 管理员信息]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function adminDetail(Request $request)
	{
		$id = $request->input('id');
		$res = admin::join('sys_admin_group','sys_admin_group.id','=','sys_admin.group_id')->select('user_name','user_tel','user_password','group_name','desc','sys_admin.id','group_id')->where('sys_admin.id',$id)->first()->toArray();
		return apiReturn(0,'管理员信息',$res);
	}
	/**
	 * [updPassword 修改密码]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function updPassword(Request $request)
	{
		$data =  $request->all();
		$arr = DB::table('sys_admin')->where('id',$data['id'])->first();
		if (md5($data['user_password'])!=$arr['user_password']) {
			return apiReturn(1,'旧密码不正确');
		}elseif ($data['user_password']==$data['new_password']) {
			return apiReturn(1,'两次密码不能一样!');
		}else{
			$res = DB::table('sys_admin')->where('id',$data['id'])->update(['user_password'=>md5($data['new_password'])]);
			if ($res==1) {
				return apiReturn(0,'操作成功！');
			}else{
				return apiReturn(1,'操作失败！');
			}
		}
	}

	/**
	 * [adminLog 管理员日志]
	 * @return [type] [description]
	 */
	public function adminLog()
	{
		$res = DB::table('sys_admin_log')->get()->toArray();
		return apiReturn(0,'管理员日志',$res);
	}
	/**
	 * [rolelist 角色下拉列表]
	 * @return [type] [description]
	 */
	public function rolelist()
	{
		$res = DB::table('sys_admin_group')->select('id','group_name')->whereNotIn('id',[7])->get()->toArray();
		return apiReturn(0,'角色下拉列表',$res);
	}


}



?>