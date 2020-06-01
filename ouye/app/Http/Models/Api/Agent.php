<?php

namespace App\Http\Models\Api;

use App\Http\Models\AgentApplyRecord;
use App\Http\Models\User;
use App\Http\Models\Task;
use App\Http\Models\TaskExclusive;
use App\Http\Models\UserAwardRecord;


/**
 * Api
 * 代理表
 */
class Agent extends \App\Http\Models\Agent
{
    /**
     * 绑定上级
     */
    public static function bindSuperior($uid, $suid)
    {
        if ($uid == $suid || empty($uid)) {
            return;
        }
        $source_agent_info = static::where('uid', $suid)->where('agent_status', 5)->first();
        if (!$source_agent_info) {
            return;
        }
        $agent_info = static::where('uid', $uid)->first();
        if ($agent_info) {
            if ($agent_info->agent_status > 1) {
                return;
            }
            $agent_info->suid = $suid;
            $agent_info->save();
        } else {
            static::create([
                'uid' => $uid,
                'suid' => $suid,
                'agent_status' => 0,
            ]);
        }
    }
    /**
     * 确认成为代理
     */
    public static function confirmAgent($agent_info, $order_info)
    {
        if ($agent_info->grade < 1) {
            return false;
        } elseif ($agent_info->grade == 1) {
            $base_agent_condition = AgentGradeCondition::where('grade', 1)
                ->whereRaw("((buy_num <= {$order_info->num} and buy_num_type = 1) or (buy_num <= {$agent_info->stock_num_accumulate} and buy_num_type = 2) or buy_num_type = 0) and ((buy_money <= {$order_info->pay_money} and buy_money_type = 1) or (buy_money <= {$agent_info->stock_pay_accumulate} and buy_money_type = 2)  or buy_money_type = 0)")
                ->first();
            if (empty($base_agent_condition)) {
                return $agent_info;
            }
        }
        $agent_info->agent_status = 5;

        AgentApplyRecord::where([
            'uid' => $agent_info->uid,
            'upgrade' => 1
        ])
            ->update([
                'status' => 5
            ]);
        User::where('id', $agent_info->uid)->update([
            'user_type' => 3,
            'user_type_check' => 0,
        ]);
        // 一级代理商特殊任务
        if ($agent_info->grade == 1) {
            $task_infos = Task::where('is_valid', 1)->whereRaw("JSON_CONTAINS(role_type, '[4]')")->get();
            $task_award_limit = 0;
            foreach ($task_infos as $key => $task_info) {
                $task_exclusive = [
                    'uid' => $agent_info->uid,
                    'task_id' => $task_info['id'],
                    'surplus_num' => $task_info['allow_num'],
                    'sum_num' => $task_info['allow_num'],
                ];
                TaskExclusive::create($task_exclusive);
                $task_award_limit += $task_info['award'] * $task_info['allow_num'];
            }
            $agent_info->task_award_limit = $task_award_limit;
        }
        // 奖励发展代理商
        if ($agent_info->suid > 0) {
            $award = Config::where('key', 'expand_agent_award')->value('value'); 
            $award_record = [
                'uid' => $agent_info->suid,
                'source' => 7,
                'source_id' => 0,
                'source_user_id' => $agent_info->uid,
                'is_bill' => 0,
                'award' => $award,
            ];
            UserAwardRecord::create($award_record);
        }

        return $agent_info;
    }

    /**
     * 代理商确认超时
     */
    public function overtimeCheck($uid, $user_type)
    {
        // 30天超时，确认代理商超时
        $overtime = time() - 86400 * 30;
        $apply_record_info = AgentApplyRecord::where([
            'uid' => $uid,
            'status' => 3,
        ])
        ->orderBy('id', 'desc')
        ->first();
        if ($apply_record_info->check_at < $overtime) {
            $this->agent_status = 6;
            $this->save();
            $apply_record_info->status = 6;
            $apply_record_info->save();
            // 用户信息
            $user_info = User::where('id', $uid)->first();
            $user_info->user_type_check = 0;
            $user_info->save();
            return $user_info->user_type;
        }
        return $user_type;
    }
}
