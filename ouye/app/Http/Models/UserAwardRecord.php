<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 用户奖励记录
 */
class UserAwardRecord extends Model
{
    use SoftDeletes;

    protected $table = 'cs_user_award_record';
    protected $dateFormat = 'U';

    protected $guarded = [];

    /**
     * 自动领取
     */
    public function autoReceive($uid)
    {
        $time = time() - 86400*2;
        $award_info = $this->where([
            ['uid', $uid],
            ['is_bill', 0],
            ['award', '>', 0],
            ['created_at', '<', $time],
        ])
        ->get();
        
        if (!empty($award_info)) {
            $bill_obj = new UserWalletBill;
            foreach ($award_info as $key => $value) {
                $bill_obj->addAwardBill($value);
            }
        }
    }
}
