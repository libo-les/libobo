<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;  
use App\Http\Models\PlatformSpokemanCondition;
use Illuminate\Support\Facades\DB;

class SpokemanConditionController extends BaseController
{
	/**
	 * [index 代言人条件列表]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function index()
	{	
		$res = PlatformSpokemanCondition::select('id','title','key','num','unit','desc','is_use')->get()->toArray();
		return  apiReturn(0,'代言人条件信息',$res);  
	}

    /**
     * [addSpokCondit 修改代言人条件]
     * @param Request $request [description]
     */
    public function addSpokCondit(Request $request)
    {
    	$data = $request->all();
        
        $list = PlatformSpokemanCondition::get()->toArray();
        if (empty($list)) {
            $arr = PlatformSpokemanCondition::insert($data);
            if ($arr==1) {
                return apiReturn(0,'添加成功');
            }else{
                 return apiReturn(0,'添加失败');
            }
            
        }else{
            foreach ($data as $key => $value) {
                $res[] = PlatformSpokemanCondition::where('key',$value['key'])->update($value); 
            }
            if (in_array(1, $res)==true) {
                return apiReturn(0,'修改成功');
            }else{
                return apiReturn(1,'修改失败');
            }
        }




    	
    	
    }

    


}
