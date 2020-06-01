<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;  
use App\Http\Models\Agent;
use App\Http\Models\Config;
use Illuminate\Support\Facades\DB;
use Excel;
use EasyWeChat\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Facades\Image;
/**
 * 代理商管理
 */
class AgentController extends BaseController
{
	/**
	 * [index 代理商列表]
	 * @return [type] [description]
	 */
   	public function index(Request $request)
   	{ 
         $perPage = 10;
         $columns = ['*'];
         $pageName = 'page';
         $currentPage = $request->input('page');
         $res = agent::join('cs_user','cs_user.id','=','cs_agent.uid')
         ->join('cs_agent_grade_condition','cs_agent.grade','=','cs_agent_grade_condition.grade')
         ->select('nickname','name','avatar','city','province','country','telephone','agent_status','grade_name','balance')
         ->whereIn('agent_status',['3','5'])->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
   		return apiReturn(0,'ok',$res);
   	}
   	/**
   	 * [agentExamine 审核代理商]
   	 * @param  Request $request [description]
   	 * @return [type]           [description]
   	 */
   	public function agentExamine(Request $request)
   	{
   		$id = $request->input('id');
      $staus = $request->input('agent_status');
      $remark = $request->input('record_remark');
      $name = $request->input('name');
      $contract = $request->input('contract');
      $mas = agent::where('id',$id)->first()->toArray();
      $stock_num = DB::table('cs_user_stock')->where('uid',$mas['uid'])->sum('stock_num');
      $accumulate_num = DB::table('cs_user_stock')->where('uid',$mas['uid'])->sum('accumulate_num');
      $res = agent::where('id',$id)->update(['agent_status'=>$staus,'name'=>$name,'contract'=>$contract]);
      $maxid = DB::table('cs_agent_apply_record')->where('uid',$mas['uid'])->max('id');
   		if ($res==1) {
          if ($staus==3) {
             agent::where('id',$id)->update(['grade'=>$mas['grade'],'stock_num'=>$stock_num,'stock_num_accumulate'=>$accumulate_num]);
              DB::table('cs_agent_apply_record')->where('id',$maxid)->update(['uid'=>$mas['uid'],'status'=>$staus,'upgrade'=>$mas['grade'],'reason'=>$mas['reason'],'contract'=>$mas['contract'],'check_at'=>time()]);
              DB::table('cs_user')->where('id',$mas['uid'])->update(['user_type_check'=>3]);
              DB::table('cs_user_spokesman')->where('uid',$mas['uid'])->delete();
          }else{
              DB::table('cs_user')->where('id',$mas['uid'])->update(['user_type_check'=>0]);
              DB::table('cs_agent_apply_record')->where('id',$maxid)->update(['uid'=>$mas['uid'],'status'=>$staus,'upgrade'=>$mas['grade'],'record_remark'=>$remark,'contract'=>$mas['contract'],'check_at'=>time()]);
          }
          return apiReturn(0,'操作成功');
      }else{

           return apiReturn(0,'操作失败');
      }
   	}
   	/**
   	 * [delAgent  删除代理商]
   	 * @param  Request $request [description]
   	 * @return [type]           [description]
   	 */
   	public function delAgent(Request $request)
   	{
   		$id = $request->input('id');
   		$res = agent::where('uid',$id)->delete();
   		if ($res==1) {
   			return apiReturn(1,'操作成功');
   		}else{
   			return  apiReturn(0,'操作失败');
   		}
   	}
      /**
       * [stayAgent 待审核代理]
       * @return [type] [json]
       */
      public function stayAgent(Request $request)
      {
         $perPage = 10;
         $columns = ['*'];
         $pageName = 'page';
         $currentPage = $request->input('page');
         $name = $request->input('name');
         $time = $request->input('created_at');
         $star=strtotime($time);
         $end = $star+(24*3600);
         if (!empty($time)&&!empty($name)) {
           $res=agent::join('cs_user','cs_user.id','=','cs_agent.uid')
           ->join('cs_agent_grade_condition','cs_agent.grade','=','cs_agent_grade_condition.grade')
           ->select('cs_agent.id','cs_user.nickname','name','cs_user.telephone','grade_name','cs_agent.created_at')
           ->where('agent_status',2)
           ->where('is_check',1)
           ->where('nickname','like','%'.$name.'%')
           ->orwhere('name','like','%'.$name.'%')
           ->orwhere('grade_name','like','%'.$name.'%')
           ->whereBetween('cs_agent.created_at',[$star,$end])
           ->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
         }elseif(!empty($time)&&empty($name)){
           $res=agent::join('cs_user','cs_user.id','=','cs_agent.uid')
           ->join('cs_agent_grade_condition','cs_agent.grade','=','cs_agent_grade_condition.grade')
           ->select('cs_agent.id','cs_user.nickname','name','cs_user.telephone','grade_name','cs_agent.created_at')
           ->whereBetween('cs_agent.created_at',[$star,$end])
           ->where('agent_status',2)
           ->where('is_check',1)
           ->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
         }elseif (empty($time)&&!empty($name)) {
            $res=agent::join('cs_user','cs_user.id','=','cs_agent.uid')
           ->join('cs_agent_grade_condition','cs_agent.grade','=','cs_agent_grade_condition.grade')
           ->select('cs_agent.id','cs_user.nickname','name','cs_user.telephone','grade_name','cs_agent.created_at')
           ->where('agent_status',2)
           ->where('is_check',1)
           ->where('nickname','like','%'.$name.'%')
           ->orwhere('name','like','%'.$name.'%')
           ->orwhere('grade_name','like','%'.$name.'%')
           ->paginate($perPage, $columns, $pageName, $currentPage)->toArray();
         }else{
          $res=agent::join('cs_user','cs_user.id','=','cs_agent.uid')
         ->join('cs_agent_grade_condition','cs_agent.grade','=','cs_agent_grade_condition.grade')
         ->select('cs_agent.id','cs_user.nickname','name','cs_user.telephone','grade_name','cs_agent.created_at')
         ->where('agent_status',2)->where('is_check',1)->paginate($perPage, $columns, $pageName, $currentPage)->toArray();

         }
          if (!empty($res)) {
              return apiReturn(0,'ok',$res);
            }else{
             
              return apiReturn(1,'暂无数据');
            }  
       
      }
      /**
       * [agentDetail 待审核代理商详情]
       * @param  Request $request [description]
       * @return [type]           [json]
       */
      public function agentDetail(Request $request)
      {
         $id = $request->input('id');
         $res = DB::table('cs_agent')
         ->join('cs_user','cs_agent.uid','=','cs_user.id')
         ->join('cs_agent_grade_condition','cs_agent.grade','=','cs_agent_grade_condition.grade')
         ->select('name','cs_user.nickname','cs_user.avatar','cs_user.total_buy_money','cs_agent.contract','cs_agent.reason','cs_agent.stock_num_accumulate','cs_agent_grade_condition.grade_name')
         ->where('cs_agent.id',$id)
         ->first();
         return apiReturn(0,'待审核代理商详情',$res);

      }
      /**
       * [setdetail 代理商设置信息]
       * @return [type] [description]
       */
      public function setdetail()
      {
         
        $res['each_box'] = DB::table('sys_config')->where('key','each_box')->value('value');
        $res['agent_least']=DB::table('sys_config')->where('key','agent_least')->value('value');
        return apiReturn(0,'ok',$res);
      }

      /**
       * [updset 修改代理商设置信息]
       * @param  Request $request [description]
       * @return [type]           [description]
       */
      public function updset(Request $request)
      {
        $each_box= $request->input('each_box');
        $agent_least= $request->input('agent_least');
        $seteach = Config::where('key','each_box')->update(['value'=>$each_box]);
        $setleast = Config::where('key','agent_least')->update(['value'=>$agent_least]);
        if ($seteach==1||$setleast==1) {
            return apiReturn(0,'修改成功');
        }else{
          return apiReturn(1,'修改失败');
        }
      }
      /**
       * [agentExport 数据导出]
       * @return [type] [description]
       */
      public function agentExport()
      {
           $name = iconv('UTF-8', 'GBK', '代理商表');
           Excel::create($name,function($excel){

            $excel->sheet('score', function($sheet){

             $data = agent::join('cs_user','cs_user.id','=','cs_agent.uid')
             ->join('cs_agent_grade_condition','cs_agent.grade','=','cs_agent_grade_condition.grade')
             ->select('nickname','real_name','avatar','city','province','country','telephone','agent_status','grade_name','balance')
             ->whereIn('agent_status',['3','5'])->get()->toArray();
             $sheet->appendRow(['用户昵称','真实姓名','头像','所在城市','代理商等级','手机号','可提现金额']);
                 foreach ($data as $key => $value) {
                    $sheet->appendRow([
                      $value['nickname'],
                      $value['real_name'],
                      $value['avatar'],
                      $value['country']. $value['province'].'省'.$value['city'].'市',
                      $value['grade_name'],
                      $value['telephone'],
                      $value['balance']
                    ]);
              }     

            });

          })->export('xls');
        
      }
      /**
       * [QRcode 代理商二维码图片]
       * @param Request $request [description]
       */
      public function QRcode(Request $request)
      {
       
        $options = [
                  // 必要配置
              'app_id'             =>config('wx_config.appid'),
              'secret'             =>config('wx_config.secret'),
          ];
          $app = Factory::officialAccount($options);
          $access_token = $app->access_token->getToken(config('wx_config.access_token_debug'));
          $response = http_request('POST', 'https://api.weixin.qq.com/wxa/getwxacode', [
            'query' => [
                'access_token' => $access_token['access_token']
            ],
            'json' => [
                 "path" => "pages/lvPromote/agent",
                 "width" =>300,
            ],
            'header'=>[
                  'content-type'=>'json',
            ],
          ]);
          $temp = md5(time()) . '.jpg';
         
          $file = Image::make($response);
          $temp = 'temp/' . date('ymd') . '/' . md5(time()) . '.jpg';
          Storage::put('./' . $temp, $response);
          $qrcode = config('app.img_url') . '/storage/' . $temp;
          return apiReturn(0,'二维码图片',$qrcode);
      }
      
}
