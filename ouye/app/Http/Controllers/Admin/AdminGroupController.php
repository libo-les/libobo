<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;  
use App\Http\Models\AdminGroup;
use Illuminate\Support\Facades\DB;


class AdminGroupController extends BaseController
{	
	/**
	 * [index 角色列表]
	 * @return [type] [description]
	 */
    public function index(Request $request)
    {   
        $perPage = 10;
        $columns = ['*'];
        $pageName = 'page';
        $currentPage = $request->input('page');
    	$res = AdminGroup::whereNotIn('id',[1])->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
    	return apiReturn(0,'角色列表',$res);
    }
    /**
     * [addGrole 角色添加]
     * @param Request $request [description]
     */
    public function addGroup(Request $request)
    {
    	$data = $request->all();
        $data['module_id_array'] = implode(',',$data['module_id_array']);
        $data['created_at']=time();
        $data['updated_at']=time();
        $res = AdminGroup::insert($data);
        if ($res==1) {
            return apiReturn(0,'添加成功');
        }else{
            return apiReturn(1,'添加失败');
        }

    }
    /**
     * [groupDetail 模块详情]
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function groupDetail()
    {
         $res= DB::table('sys_module')->select('id','module_name','controller','method','pid','level','url','is_menu','is_dev','route')->get()->toArray();
             $items = array();
            foreach($res as $value){
                $items[$value['id']] = $value;
            }
            $tree = array();
            foreach($items as $key => $value){
    //如果pid这个节点存在
                if(isset($items[$value['pid']])){
            //把当前的$value放到pid节点的son中
                  $items[$value['pid']]['son'][] = &$items[$key];
              }else{
                  $tree[] = &$items[$key];
              }
          }
          return apiReturn(0,'权限分配',$tree);
          
           
     }
     /**
      * [groupMessage 角色信息]
      * @param  Request $request [description]
      * @return [type]           [description]
      */
     public function groupMessage(Request $request)
     {
        $id = $request->input('id');
        $res = AdminGroup::where('id',$id)->first()->toArray();
        $module = explode(',',$res['module_id_array']);
        $tab = DB::table('sys_module')->get()->toArray();
            foreach ($tab as $key => $value) {
                    if (in_array($value['id'],$module)&&$value['level']==1) {
                         $tab[$key]['isAll']=1;
                    }elseif (!in_array($value['id'],$module)&&$value['level']==1) {
                        $tab[$key]['isAll']=0;
                    }elseif (in_array($value['id'],$module)&&$value['level']==2) {
                         $tab[$key]['isSelection']=1;
                    }elseif (!in_array($value['id'],$module)&&$value['level']==2) {
                        $tab[$key]['isSelection']=0;
                    }elseif (in_array($value['id'],$module)&&$value['level']==3) {
                         $tab[$key]['isSelection']=1;
                    }else{
                        $tab[$key]['isSelection']=0;
                    }
            }
            $items = array();
            foreach($tab as $value){
                $items[$value['id']] = $value;
            }
            $tree = array();
            foreach($items as $key => $value){
    //如果pid这个节点存在
                if(isset($items[$value['pid']])){
            //把当前的$value放到pid节点的son中
                  $items[$value['pid']]['son'][] = &$items[$key];
              }else{
                  $tree[] = &$items[$key];
              }
          }
            foreach ($tree as $key => $value) {
               if ($value['level']!=1) {
                   unset($tree[$key]);
               }
            }
            $tree = array_values($tree);
         $data = array('group_name' => $res['group_name'],'desc'=>$res['desc'],'module'=>$tree);
            return apiReturn(0,'角色信息',$data);
     }
     /**
      * [updGroup 修改角色权限]
      * @param  Request $request [description]
      * @return [type]           [description]
      */
     public function updGroup(Request $request)
     {
        $data = $request->all();
        $data['module_id_array'] = implode(',',$data['module_id_array']);

        $res = AdminGroup::where('id',$data['id'])->update($data);
         if ($res==1) {
            return apiReturn(0,'修改成功');
        }else{
            return apiReturn(1,'修改失败');
        }
     }

    /**
     * [delGroup 删除角色]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function delGroup(Request $request)
    {
        $id = $request->input('id');
        $isman = DB::table('sys_admin')->where('group_id',$id)->get()->toArray();
        if (!empty($isman)) {
            return apiReturn(1,'该角色已被使用，无法删除');
        }
        $res = AdminGroup::where('id',$id)->delete();
        if ($res==1) {
            return apiReturn(0,'ok');
        }else{
            return apiReturn(0,'no');
        }
    }

}
