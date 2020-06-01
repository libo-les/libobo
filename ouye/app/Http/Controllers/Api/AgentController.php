<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Validator;

use App\Http\Models\AgentApplyRecord;
use App\Http\Models\UserStock;
use App\Http\Models\UserStockRecord;
use App\Http\Models\TaskExclusive;
use App\Http\Models\Api\UserStockOrder;
use App\Http\Models\Config;
use App\Http\Models\Api\Agent;
use App\Http\Models\User;
use App\Http\Models\Api\UserSpokesman;
use App\Http\Models\Api\AgentGradeCondition;

/**
 * 代理
 */
class AgentController extends Controller
{
    /**
     * 申请代理商条件
     */
    public function applyCondition(Request $request)
    {
        $uid = $request->token_info['uid'];
        $grade = $request->input('grade', 1);
        
        // 分享绑定
        if (!empty($request->scode) && $grade == 1) {
            $scode = decrypt($request->scode);
            $suid = $scode['uid'];
            Agent::bindSuperior($uid, $suid);
        }

        $condition = AgentGradeCondition::where('grade', $grade)->first();
        $agent_info = Agent::where('uid', $uid)->first();

        $accumulate = UserStock::where([
            'uid' => $uid,
        ])
            ->selectRaw('sum(accumulate_money) money_accumulate, sum(accumulate_num) stock_num_accumulate')
            ->first();
        $money_accumulate = $accumulate['money_accumulate'];
        $stock_num_accumulate = $accumulate['stock_num_accumulate'];

        return apiReturn(0, 'ok', compact('condition', 'agent_info', 'money_accumulate', 'stock_num_accumulate'));
    }

    /**
     * 代理申请
     */
    public function apply(Request $request)
    {
        $rules = [
            'name' => 'required',
        ];
        $messages = [
            'name.required' => '名字必填',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return apiReturn(1, $validator->errors()->first());
        }
        $data = $request->except('token_info', 's');

        $uid = $request->token_info['uid'];
        $apent_obj = new Agent;
        $agent_info = $apent_obj->where('uid', $uid)->first();
        if (!empty($agent_info) && in_array($agent_info->agent_status, [2, 3, 5])) {
            return apiReturn(1, '请不要重复提交');
        }
        // 库存数量
        $accumulate = UserStock::where([
            'uid' => $uid,
        ])
            ->selectRaw('sum(stock_num) stock_num_new, sum(accumulate_money) money_accumulate, sum(accumulate_num) stock_num_accumulate')
            ->first();

        $stock_num_new = $accumulate['stock_num_new'] ?? 0;
        $stock_pay_accumulate = $accumulate['money_accumulate'] ?? 0;
        $stock_num_accumulate = $accumulate['stock_num_accumulate'] ?? 0;

        if (empty($agent_info)) {
            $apent_obj->uid = $uid;
            $apent_obj->suid = $data['suid'] ?? 0;
            $apent_obj->agent_status = 2;
            $apent_obj->name = $data['name'];
            $apent_obj->contract = $data['contract']??0;
            $apent_obj->reason = $data['reason'];
            $apent_obj->stock_num = $stock_num_new;
            $apent_obj->stock_num_accumulate = $stock_num_accumulate;
            $apent_obj->stock_pay_accumulate = $stock_pay_accumulate;
            $apent_obj->grade = 1;
            $apent_obj->save();
        } else {
            $agent_info->suid = $data['suid'] ?? 0;
            $agent_info->name = $data['name'];
            $agent_info->contract = $data['contract']??0;
            $agent_info->reason = $data['reason'];
            $agent_info->stock_num = $stock_num_new;
            $agent_info->stock_num_accumulate = $stock_num_accumulate;
            $agent_info->stock_pay_accumulate = $stock_pay_accumulate;
            $agent_info->agent_status = 2;
            $agent_info->save();
        }

        $data['uid'] = $uid;
        $data['status'] = 2;
        $result = AgentApplyRecord::create($data);

        return apiReturn(0, 'ok', $result);
    }
    /**
     * 代理信息
     */
    public function index(Request $request)
    {
        // 批量取消库存付款
        UserStockOrder::batchCancelSupplement();

        $uid = $request->token_info['uid'];
        $agent_info = Agent::with('user')->where('uid', $request->token_info['uid'])->first();
        $agent_info->grade_name = AgentGradeCondition::where('grade', $agent_info->grade)->value('grade_name');
        // DB::enableQueryLog();
        // 今日销量
        $today_sales = UserStockOrder::where([
            'seller_uid' => $uid,
        ])
            ->whereIn('order_status', [3, 5, 6, 7, 12, 13])
            ->WhereBetween('created_at', [strtotime('today'), strtotime('tomorrow')])
            ->sum('num');
        // 我的代言人数量
        $spokesman_num = UserSpokesman::where([
            'suid' => $uid,
            'spokesman_status' => 4
        ])->count();
        // 我的代言人销售排名
        $sales_rank = UserStockOrder::where([
            'seller_uid' => $uid,
            'buy_user_type' => 2,
        ])
            ->whereIn('order_status', [3, 5, 6, 7, 12, 13])
            ->selectRaw('sum(num) buy_num, buyer_id')
            ->groupBy('buyer_id')
            ->orderBy('buy_num', 'desc')
            ->paginate(20);
        foreach ($sales_rank as $key => &$item) {
            $user_info = User::where('id', $item->buyer_id)->first();
            $item->nickname = $user_info->nickname;
            $item->avatar = $user_info->avatar;
            $item->check_at = UserSpokesman::where('uid', $item->buyer_id)->value('check_at');
        }

        // var_dump(DB::getQueryLog());die;
        return apiReturn(0, 'ok', compact('agent_info', 'today_sales', 'spokesman_num', 'sales_rank'));
    }

    /**
     * 代理商任务中心
     */
    public function agentTask(Request $request)
    {
        $uid = $request->token_info['uid'];

        $task_list = TaskExclusive::where('uid', $uid)->with('task')->get();
        // 
        $one_grade_name = AgentGradeCondition::where('id', 1)->value('grade_name');
        // 奖励信息
        $award_info['expand_spokesman_award'] = Config::where('key', 'expand_spokesman_award')->value('value');
        $award_info['expand_agent_award'] = Config::where('key', 'expand_agent_award')->value('value');
        // 发展代言人
        $expand_num['man'] = UserSpokesman::where('suid', $uid)->where('spokesman_status', 4)->count();
        // 发展代理数
        $expand_num['agent'] = Agent::where('suid', $uid)->count();

        return apiReturn(0, 'ok', compact('one_grade_name', 'task_list', 'award_info', 'expand_num'));
    }

    /**
     * 代理商个人中心
     */
    public function personalCenter(Request $request)
    {
        $uid = $request->token_info['uid'];

        $agent_info = Agent::where('uid', $uid)->where('agent_status', 5)->first();
        if ($agent_info) {
            $condition = AgentGradeCondition::where('grade', $agent_info->grade + 1)->where('is_check', 1)->first();
        }

        return apiReturn(0, 'ok', compact('condition'));
    }

    /**
     * 代理商提交升级信息
     */
    public function applyUpgrade(Request $request)
    {
        $uid = $request->token_info['uid'];

        $agent_info = Agent::where('uid', $uid)->where('agent_status', 5)->first();
        // 库存数量
        $accumulate = UserStock::where([
            'uid' => $uid,
        ])
            ->selectRaw('sum(accumulate_money) money_accumulate, sum(accumulate_num) stock_num_accumulate')
            ->first();
        $stock_pay_accumulate = $accumulate['money_accumulate'];
        $stock_num_accumulate = $accumulate['stock_num_accumulate'];
        // 条件验证
        $upgrade = $agent_info->grade + 1;
        $agent_upgrade_info = AgentGradeCondition::where('is_check', 1)
            ->where('grade', $upgrade)
            ->whereRaw("((buy_num <= {$stock_num_accumulate} and buy_num_type = 2) or buy_num_type = 0) and ((buy_money <= {$stock_pay_accumulate} and buy_money_type = 2)  or buy_money_type = 0)")
            ->first();
        if (empty($agent_upgrade_info)) {
            return apiReturn(1);
        }

        $data = $request->except('token_info');
        $data['uid'] = $uid;
        $data['status'] = 2;
        $data['upgrade'] = $upgrade;
        $result = AgentApplyRecord::create($data);

        return apiReturn();
    }
}
