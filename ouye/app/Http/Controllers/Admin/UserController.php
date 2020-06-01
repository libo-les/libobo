<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Model;  
use App\Http\Models\User;
use Excel;

/**
 * 用户管理
 */
class UserController extends BaseController
{
	/**
	 * [index 用户列表]
	 * @param  Request $request [description]
	 * @return [type]           [json]
	 */
	public function index(Request $request)
	{	
		$perPage = 10;
		$columns = ['*'];
		$pageName = 'page';
		$currentPage = $request->input('page');
		$times = $request->input('created_at');
		$start = strtotime($times);
		$end = ($start+3600*24)-1;
		$name = $request->input('nickname');
		if (!empty($times)&&!empty($name)) {
			$res = user::where('nickname','like','%'.$name.'%')
			->whereBetween('created_at',[$start,$end])
			->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
		}elseif (!empty($times)&&empty($name)) {

			$res = user::whereBetween('created_at',[$start,$end])->paginate($perPage, $columns, $pageName, $currentPage)->toArray();	
		}elseif(empty($times)&&!empty($name)){

			$res = user::where('nickname','like','%'.$name.'%')
			->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
		}else{
			$res = user::paginate($perPage, $columns, $pageName, $currentPage)->toArray();
		}
		return apiReturn(0,'用户列表',$res);
	}

	public function UserExport()
    {  
    	
        $name = iconv('UTF-8', 'GBK', '用户信息表');
        Excel::create($name,function($excel){

            $excel->sheet('score', function($sheet){
                $data = DB::table('cs_user')->get()->toArray();
                $sheet->appendRow(['用户昵称','用户头像','性别','电话号码','添加时间']);
                foreach ($data as $key => $value) {
                    if ($value['gender']==1) {
                      $sheet->appendRow([$value['nickname'],$value['avatar'],'男',$value['telephone'],date('Y-m-d H:i:s',$value['created_at'])]);
                    }elseif ($value['gender']==2) {
                       $sheet->appendRow([$value['nickname'],$value['avatar'],'女',$value['telephone'],date('Y-m-d H:i:s',$value['created_at'])]);
                    }else{
                        $sheet->appendRow([$value['nickname'],$value['avatar'],'未知',$value['telephone'],date('Y-m-d H:i:s',$value['created_at'])]);
                    }
                }     

            });

        })->export('xls');
    
    }


}



?>