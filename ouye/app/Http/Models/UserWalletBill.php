<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ApiException;

use App\Http\Models\User;

class UserWalletBill extends Model
{
    protected $table = 'cs_user_wallet_bill';
    protected $dateFormat = 'U';

    protected $guarded = [];

    /**
     * 统计可提现余额
     */
    public function statisticBalance($uid, $fix = false)
    {
        $balance = $this->where([
            'uid' => $uid,
            'settlement_id' => 0,
        ])
        ->sum('money_deal');
        $balance = $balance ?? 0;
        if ($fix == true) {
            User::where('id', $uid)->update(['balance' => $balance]);
        }

        return $balance;
    }
    /**
     * 获取奖励添加账单表
     */
    public function addAwardBill($award_info)
    {
        $uid = $award_info->uid;
        $bill_num = $this->where('award_id', $award_info->id)->count();
        if ($bill_num > 0) {
            throw new ApiException('该奖励已经领取过', 90001);
        }
        // 开始领取奖励
        DB::beginTransaction();
        try {
            $award_info->is_bill = 1;
            $award_info->save();
    
            if ($award_info->goods_id) {
                UserStock::where('uid', $uid)->where('goods_id', $award_info->goods_id)->increment('award_accumulate', $award_info->award);
            }
            // 添加用户资金账单
            $balance = $this->statisticBalance($uid);

            $data = [
                'uid' => $uid,
                'award_type' => $award_info->source,
                'source' => 1,
                'source_id' => $award_info->source_id,
                'source_user_id' => $award_info->source_user_id,
                'deal_type' => 1,
                'money_deal' => $award_info->award,
                'money_total' => $balance + $award_info->award,
                'award_id' => $award_info->id,
            ];
            $bill_res = $this->create($data);

            User::where('id', $uid)->increment('total_award', $award_info->award);
            User::where('id', $uid)->increment('balance', $award_info->award);
            DB::commit();

            return apiReturn(0, 'ok', $bill_res);
        } catch (\Exception $e) {
            $code = is_integer($e->getCode()) ? $e->getCode() : 90900;
            throw new ApiException($e->getMessage(), $code);
        }
    }
}
