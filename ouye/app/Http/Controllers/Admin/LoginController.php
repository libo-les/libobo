<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query;
use Illuminate\Database\Eloquent\Model;  
use App\Http\Models\Admin;
use App\Http\Models\Module;
use Captcha;
use Illuminate\Support\Facades\Validator;

/**
 * 登录
 */
class LoginController extends Controller
{	
	/**
	 * [index 后台登录]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function index(Request $request)
	{
		
		$data = $request->all();
		$ip = $request->ip();
		if (!captcha_api_check($data['captcha'],$data['key'])){
			return apiReturn(1,'验证码不匹配');
		}	
		
		$res = admin::where('user_name', $data['user_name'])->first();
		
		if (!empty($res)) {
			$password = md5($data['user_password']);
			if ($password==$res['user_password']) {
				$num = $res['login_num']+1;
				$now = time();
				$user=Admin::where('user_name',$data['user_name'])->update(['login_num' => $num,'current_login_time'=>$now,'updated_at'=>$now,'current_login_ip'=>$ip,'last_login_time'=>$res['current_login_time'],'last_login_ip'=>$res['current_login_ip']]);
				if ($user==1) {
					session(['admin_id' => $res['id']]);
					$act_lists = Db::table('sys_admin_group')->where('id',$res['group_id'])->value('module_id_array');
					session(['act_lists' => $act_lists]);
					$list = getmenuList(session('act_lists'));
					$lists = array_values($list);
					return apiReturn(0,'登录成功',$lists[0]['sub_menu'][0]['path']);
				}else{
					return apiReturn(1,'登录失败');
				}
			}else{
				return apiReturn(1,'密码错误');
			}
			
		}else{
			return apiReturn(1,'用户名错误');
		}
	}

	/**
	 * [logOut 退出登录]
	 * @return [type] [description]
	 */
	public function logOut(Request $request)
	{
		$request->session()->forget('admin_id');
		$request->session()->forget('act_lists');
		if ($request->session()->has('admin_id')==true) {
    		return apiReturn(1,'退出失败!');
		}else{
			return apiReturn(0,'退出成功!');
		}		
	}
	
	/**
	 * [headnews 头部信息]
	 * @return [type] [description]
	 */
	public function headnews()
	{
		$admin_id = session('admin_id');
		if (empty($admin_id)) {
			return apiReturn(10004,'没有登录！');
		}
		$list = getmenuList(session('act_lists'));
		$lists = array_values($list);
		$res = DB::table('sys_admin')->join('sys_admin_group','sys_admin.group_id','=','sys_admin_group.id')
		->select('sys_admin.id','sys_admin.last_login_time','sys_admin_group.group_name','sys_admin.user_name')
		->where('sys_admin.id',$admin_id)
		->first();
		$data=array('massage'=>$res,'powerlist'=>$lists,'sevre'=>config('app.url'));
		return apiReturn(0,'ok',$data);
	}
	/**
	 * [modifyCipher 修改自己密码]
	 * @param  Request $request [description]
	 * @return [type]           [json]
	 */
	public function modifyCipher(Request $request)
	{	
		$data =  $request->all();
		$admin_id = session('admin_id');
		$arr = DB::table('sys_admin')->where('id',$admin_id)->first();
		if (md5($data['user_password'])!=$arr['user_password']) {
			return apiReturn(1,'旧密码不正确');
		}elseif ($data['user_password']==$data['new_password']) {
			return apiReturn(1,'两次密码不能一样!');
		}else{
			$res = DB::table('sys_admin')->where('id',$admin_id)->update(['user_password'=>md5($data['new_password'])]);
			if ($res==1) {
				return apiReturn(0,'操作成功！');
			}else{
				return apiReturn(1,'操作失败！');
			}
		}
	}


}









?>