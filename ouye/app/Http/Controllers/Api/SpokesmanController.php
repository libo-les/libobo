<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exceptions\ApiException;
use Illuminate\Support\Facades\DB;

use App\Http\Models\OrderGoods;
use App\Http\Models\UserAwardRecord;
use App\Http\Models\User;
use App\Http\Models\UserStock;
use App\Http\Models\UserWalletBill;
use App\Http\Models\PlatformSpokemanCondition;
use App\Http\Models\Api\Order;
use App\Http\Models\Api\UserSpokesman;
use App\Http\Models\Agent;
use App\Http\Models\Config;

/**
 * 代言人
 */
class SpokesmanController extends Controller
{
        /**
     * 代言人首页
     */
    public function index(Request $request)
    {
        $uid = $request->token_info['uid'];

        $award_obj = new UserAwardRecord;
        // 批量领取奖励
        $award_obj->autoReceive($uid);

        $award = $award_obj->where([
            'uid' => $uid,
            'is_bill' => 0,
        ])->select('id', 'source', 'award', 'created_at')
            ->get();
        // 今日返利
        $today_bonus = $award_obj->where([
            'uid' => $uid,
            'source' => 1,
        ])
            ->WhereBetween('created_at', [strtotime('today'), strtotime('tomorrow')])
            ->value('award');
        $user_info = User::where('id', $uid)->select('total_award', 'balance')->first();
        // 我的库存
        $total_stock = UserStock::where('uid', $uid)->sum('stock_num');
        // 我的伙伴
        $total_partner = UserSpokesman::where('suid', $uid)->where('spokesman_status', 4)->count();
        // 补货提醒
        $stock_warn = UserStock::where([
            ['uid', $uid],
            ['stock_num', 0]
        ])
            ->count();

        return apiReturn(0, 'ok', compact('award', 'today_bonus', 'user_info', 'total_stock', 'total_partner', 'stock_warn'));
    }

    /**
     * 获得分享码
     */
    public function getScode(Request $request)
    {
        $uid = $request->token_info['uid'];

        // 判断是否是代言人
        $man_info = UserSpokesman::where([
            'uid' => $uid,
            'spokesman_status' => 4,
        ])->count();
        $agent_info = Agent::where([
            'uid' => $uid,
            'agent_status' => 5,
        ])->count();
        if (($man_info + $agent_info) == 0) {
            return apiReturn(1, '您还不是代言人');
        }

        // 生产分享码
        $scode = encrypt([
            'uid' => $uid,
        ]);

        return apiReturn(0, 'ok', compact('scode'));
    }

    /**
     * 代言人申请条件
     */
    public function applyCondition(Request $request)
    {
        $condition = PlatformSpokemanCondition::where('is_use', 1)->get();

        DB::enableQueryLog();

        return apiReturn(0, 'ok', compact('condition'));
    }
    /**
     * 代言人申请条件达成
     */
    public function conditionComplete(Request $request)
    {
        $uid = $request->token_info['uid'];
        
        // 分享绑定
        if (!empty($request->scode)) {
            $scode = decrypt($request->scode);
            $suid = $scode['uid'];
            UserSpokesman::bindSuperior($request->token_info['uid'], $request->token_info['uaid'], $suid);
        }

        $man_info['total_num'] = OrderGoods::where([
            'buyer_id' => $uid,
        ])
            ->whereIn('order_status', [6, 7, 13])
            ->sum('num');

        $man_info['total_money'] = OrderGoods::where([
            'buyer_id' => $uid,
        ])
            ->whereIn('order_status', [6, 7, 13])
            ->sum('pay_money');

        return apiReturn(0, 'ok', compact('man_info'));
    }

    /**
     * 申请成为代言人
     */
    public function apply(Request $request)
    {
        $uid = $request->token_info['uid'];

        $user_info = User::where('id', $uid)->first();
        if ($user_info['user_type'] != 1) {
            return apiReturn(1, '不能申请成为代言人');
        }

        $conditions = PlatformSpokemanCondition::where([
            'is_use' => 1,
        ])
            ->get();

        foreach ($conditions as $key => $condition) {
            switch ($condition['key']) {
                case 'total_num':
                    $total_num = OrderGoods::where([
                        'buyer_id' => $uid,
                    ])
                        ->whereIn('order_status', [6, 7, 13])
                        ->sum('num');
                    if ($total_num < $condition['num']) {
                        return apiReturn(1, "购买数量大于{$condition['num']}{$condition['unit']}，才能成为代言人");
                    }

                    break;
                case 'total_money':
                    $total_money = Order::where([
                        'buyer_id' => $uid,
                    ])
                        ->whereIn('order_status', [6, 7, 13])
                        ->sum('order_money');
                    if ($total_money < $condition['num']) {
                        return apiReturn(1, "购买累计购买金额大于{$condition['num']}{$condition['unit']}，才能成为代言人");
                    }

                    break;
                default:

                    break;
            }
        }
        $man_info = UserSpokesman::where('uid', $uid)->first();

        if (!empty($man_info)) {
            switch ($man_info['spokesman_status']) {
                case 3:
                    return apiReturn(1, '请耐心等待，正在审核中');
                    break;
                case 4:
                    return apiReturn(1, '您已经是代言人');
                    break;
                default:
                    $man_info->spokesman_status = 4;
                    $man_info->check_at = time();
                    $man_info->save();
                    if ($man_info->suid > 0) {
                        $award = Config::where('key', 'expand_spokesman_award')->value('value');
                        if ($award) {
                            $award_record = [
                                'uid' => $man_info->suid,
                                'source' => 3,
                                'source_id' => 0,
                                'source_user_id' => $uid,
                                'is_bill' => 0,
                                'award' => $award,
                            ];
                            UserAwardRecord::create($award_record);
                        }
                        UserSpokesman::where('uid', $man_info->suid)->increment('sub_man_num');
                    }
                    break;
            }
        } else {
            UserSpokesman::create([
                'ag_id' => 0,
                'uid' => $uid,
                'suid' => 0,
                'spokesman_status' => 4,
                'check_at' => time(),
            ]);
        }
        $user_info->user_type = 2;
        $user_info->save();

        return apiReturn(0, '申请成功');
    }

    /**
     * 领取奖励
     */
    public function receiveAward(Request $request)
    {
        if (empty($award_id = $request->award_id)) {
            return apiReturn(20000);
        }
        $uid = $request->token_info['uid'];
        // DB::enableQueryLog();
        $award_info = UserAwardRecord::where([
            ['uid', $uid],
            ['id', $award_id],
            ['is_bill', 0],
            ['award', '>', 0],
        ])->first();
        // var_dump(DB::getQueryLog());die;
        if (empty($award_info)) {
            return apiReturn(1, '该奖励已经领取过');
        }
        
        // 开始领取奖励
        return (new UserWalletBill)->addAwardBill($award_info);
    }
    /**
     * 我的伙伴
     */
    public function myPartner(Request $request)
    {
        $uid = $request->token_info['uid'];

        $lists = UserSpokesman::where('cs_user_spokesman.suid', $uid)
            ->where('cs_user_spokesman.spokesman_status', 4)
            ->select('u.nickname', 'u.gender', 'u.avatar', 'u.wechat', 'u.total_buy_money', 'u.total_award', 'cs_user_spokesman.sub_man_num', 'cs_user_spokesman.created_at')
            ->join('cs_user as u', 'cs_user_spokesman.uid', 'u.id')
            ->paginate(20);

        return apiReturn(0, 'ok', $lists);
    }
}
