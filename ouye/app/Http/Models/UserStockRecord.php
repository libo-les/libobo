<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Models\Config;


/**
 * 用户赏金记录
 */
class UserStockRecord extends Model
{
    protected $table = 'cs_user_stock_record';
    protected $dateFormat = 'U';
    protected $guarded = ['id'];

    public function goods()
    {
        return $this->hasOne('App\Http\Models\Goods', 'id', 'goods_id');
    }

    /**
     * 代言人分红
     */
    public static function bonus()
    {
        $bonus_scale = Config::where('key', 'bonus_scale')->value('value');
        if ($bonus_scale <= 0) {
            return;
        }
        DB::table('cs_user_stock as s')
            ->select('s.*')
            ->selectRaw('sum(s.stock_num) total_stock_num')
            ->join('cs_user as u', 's.uid', '=', 'u.id')
            ->where('u.user_type', '=', 2)
            ->having('total_stock_num', '>', 0)
            ->groupBy('s.uid')
            ->orderBy('s.id')
            ->chunk(100, function ($stocks) use ($bonus_scale) {
                foreach ($stocks as $stock) {
                    $uid = $stock['uid'];
                    // 检验一天只奖励一次
                    $is_award = UserAwardRecord::where('uid', $uid)->where('source', 1)->WhereBetween('created_at', [strtotime('today'), strtotime('tomorrow')])->count();
                    if ($is_award) {
                        continue;
                    }
                    // 奖励金额
                    $stock_infos = DB::table('cs_user_stock as s')
                        ->selectRaw('sum(s.stock_num * g.promotion_price) as total_stock_monay')
                        ->join('cs_goods as g', 's.goods_id', '=', 'g.id')
                        ->where('s.stock_num', '>', 0)
                        ->where('s.uid', $uid)
                        ->first();
                    $award = $bonus_scale * $stock_infos['total_stock_monay'] / 100;
                    if ($award <= 0) {
                        continue;
                    }

                    $award_record = [
                        'uid' => $uid,
                        'source' => 1,
                        'is_bill' => 0,
                        'award' => $award,
                    ];
                    UserAwardRecord::create($award_record);
                }
            });
    }
}
