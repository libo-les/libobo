<?php

namespace App\Http\Models\Api;

/**
 * Api
 * 升级代理条件
 */
class AgentGradeCondition extends \App\Http\Models\AgentGradeCondition
{
    /**
     * 代理升级信息
     */
    public static function upgradeAgentInfo($agent_info, $order_info)
    {
        $agent_grade_info = false;
        for ($i = 1; $i <= 2; $i++) {
            $upgrade = $agent_info->grade + $i;
            $agent_upgrade_info = AgentGradeCondition::where('is_check', 0)
                ->where('grade', $upgrade)
                ->whereRaw("((buy_num <= {$order_info->num} and buy_num_type = 1) or (buy_num <= {$agent_info->stock_num_accumulate} and buy_num_type = 2) or buy_num_type = 0) and ((buy_money <= {$order_info->pay_money} and buy_money_type = 1) or (buy_money <= {$agent_info->stock_pay_accumulate} and buy_money_type = 2)  or buy_money_type = 0)")
                ->first();
            if (empty($agent_upgrade_info)) {
                return $agent_grade_info;
                break;
            }
            $agent_grade_info = $agent_upgrade_info;
        }
        return $agent_grade_info;
    }
}
