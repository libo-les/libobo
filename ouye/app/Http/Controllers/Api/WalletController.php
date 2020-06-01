<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Models\Bank;
use App\Http\Models\User;
use App\Http\Models\UserBandCard;
use App\Http\Models\UserCashRecord;
use App\Http\Models\UserWalletBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ApiException;
use App\Http\Models\Config;
use Illuminate\Support\Facades\Log;
use App\Jobs\DepositJob;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Http\Models\UserStockRecord;

/**
 * 余额
 */
class WalletController extends Controller
{
    /**
     * 我的余额
     */
    public function balance(Request $request)
    {
        $uid = $request->token_info['uid'];

        $balance = User::where('id', $uid)->value('balance');

        return apiReturn(0, 'ok', compact('balance'));
    }

    /**
     * 明细
     */
    public function record(Request $request)
    {
        $uid = $request->token_info['uid'];

        $record_type = $request->input('record_type', 1);

        // DB::enableQueryLog();

        $wallet_obj = UserWalletBill::where('uid', $uid)
            ->where('is_public', 1);

        switch ($record_type) {
            case 1:
                $wallet_obj->whereIn('source', [1, 3, 5]);
                break;
            case 2:
                $wallet_obj->whereIn('source', [2, 4]);
                break;
            default:
                break;
        }
        $lists = $wallet_obj->paginate(20);

        // var_dump(DB::getQueryLog());die;

        return apiReturn(0, 'ok', compact('lists'));
    }

    /**
     * 提现结账单
     */
    public function depositStatement(Request $request)
    {
        $uid = $request->token_info['uid'];

        $card_info = UserBandCard::where('uid', $uid)->where('is_default', 1)->first();

        if ($card_info) {
            $bank_info = Bank::where('id', $card_info->bank)->select('icon', 'title')->first();
            $card_info->bank_title = $bank_info->title;
            $card_info->bank_icon = config('app.img_url') . $bank_info->icon;
        }

        $balance = User::where('id', $uid)->value('balance');

        return apiReturn(0, 'ok', compact('card_info', 'balance'));
    }
    /**
     * 选择银行
     */
    public function selectBank()
    {
        $bank = Bank::get()
            ->each(function ($itme, $key)
            {
                $itme->icon = config('app.img_url') . $itme->icon;
            });

        return apiReturn(0, 'ok', compact('bank'));
    }

    /**
     * 添加银行卡
     * 没有修改，解绑重新添加
     */
    public function addCard(Request $request)
    {
        $rules = [
            'bank' => 'required',
            'card_no' => 'required',
            'card_name' => 'required',
            'prov' => 'required',
            'city' => 'required',
            'open_bank' => 'required',
            'telephone' => 'required',
        ];
        $msg = [
            'bank.required' => '请选择银行',
            'card_no.required' => '请填写银行卡',
            'card_name.required' => '请填写真实姓名',
            'prov.required' => '请选择省',
            'city.required' => '请选择市',
            'open_bank.required' => '请填写开户行',
            'telephone.required' => '请填写预留电话',
        ];
        $this->validate($request, $rules, $msg);

        $user_ap_id = $request->token_info['uaid'];
        $uid = $request->token_info['uid'];
        $verify = $request->verify;
        $telephone = $request->telephone;
        if (empty($verify) || empty($telephone)) {
            return apiReturn(20001);
        }

        $result = checkVerify($user_ap_id, $telephone, $verify, 'addcard');
        if (!$result) {
            return apiReturn(1, '验证码错误');
        }
        if ($request->is_default == 1) {
            UserBandCard::where('uid', $uid)->update([
                'is_default' => 0,
            ]);
        }

        $user_band_card = [
            'uid' => $uid,
            'bank' => $request->bank,
            'card_no' => $request->card_no,
            'card_name' => $request->card_name,
            'prov' => $request->prov,
            'city' => $request->city,
            'open_bank' => $request->open_bank,
            'telephone' => $request->telephone,
            'is_default' => $request->is_default ?? 0,
        ];
        UserBandCard::create($user_band_card);

        return apiReturn(0);
    }
    /**
     * 解绑银行卡
     */
    public function unbindCard(Request $request)
    {
        $uid = $request->token_info['uid'];
        $card_id = $request->card_id;
        
        $res = UserBandCard::where('id', $card_id)
        ->where('uid', $uid)
        ->delete();

        return $res ? apiReturn() : apiReturn(1);
    }


    /**
     * 用户银行卡列表
     */
    public function userBank(Request $request)
    {
        $uid = $request->token_info['uid'];

        $user_bank = UserBandCard::where('uid', $uid)
        ->get()
        ->each(function ($itme, $key)
        {
            $bank_info = Bank::where('id', $itme->bank)->select('icon', 'title')->first();
            $itme->icon = config('app.img_url') . $bank_info->icon;
            $itme->bank = $bank_info->title;
        });

        return apiReturn(0, 'ok', compact('user_bank'));
    }

    /**
     * 设置默认银行卡
     */
    public function setDefault(Request $request)
    {
        if (empty($card_id = $request->card_id)) {
            return apiReturn(20000);
        }
        $uid = $request->token_info['uid'];

        $cancel_res = UserBandCard::where('uid', $uid)->update([
            'is_default' => 0,
        ]);

        $result = UserBandCard::where('uid', $uid)
            ->where('id', $card_id)
            ->update([
                'is_default' => 1,
            ]);

        return apiReturn(0);
    }

    /**
     *提现
     *状态 1审核中 2处理中 3提现失败 4提现成功
     */
    public function deposit(Request $request)
    {
        if (empty($bank_id = $request->bank_id)) {
            return apiReturn(20000);
        }
        if (empty($cash = $request->cash)) {
            return apiReturn(20000);
        }
        if ($cash <= 0) {
            return apiReturn(20001);
        }
        $uid = $request->token_info['uid'];

        $bank_info = UserBandCard::where('uid', $uid)->where('id', $bank_id)->first();
        if (empty($bank_info['card_no']) || empty($bank_info['card_name']) || empty($bank_info['prov']) || empty($bank_info['city']) || empty($bank_info['open_bank']) || empty($bank_info['telephone'])) {

            return apiReturn(90001, '银行卡信息不全');
        }

        // 检查未处理的提现
        $last_cash_record = UserCashRecord::where('uid', $uid)
            ->orderBy('id', 'desc')
            ->first();

        if ($last_cash_record['status'] == 1 || $last_cash_record['status'] == 2) {
            return apiReturn(90003, '上一次提现正在处理中，请稍后再试');
        }
        $last_time = $last_cash_record['created_at'] ?? 0;
        $end_time = time();

        $cash_interval = 86400;
        if ($last_time >= $end_time || $end_time - $last_time < $cash_interval) {
            return apiReturn(90005, '结算：距上次结算时间小于24小时');
        }

        // 查上次结算后的订单
        $map = [
            'settlement_id' => 0,
            'uid' => $uid,
        ];
        $user_wallet_bill_obj = new UserWalletBill;
        $bill = $user_wallet_bill_obj->where($map)
            ->select('id', 'money_deal')
            ->orderBy('id', 'desc')
            ->get();
        if ($bill->isEmpty()) {
            return apiReturn(90010, '提现金额为0元');
        }
        DB::beginTransaction();
        try {
            $ids = [];
            $money_deals = 0.00;
            foreach ($bill as $v) {
                $ids[] = $v['id'];
                $money_deals += $v['money_deal'];
            }
            // 数字太小不生成结算单
            // if ($money_deals < 5) {
            //     throw new ApiException('订单金额暂不能结算', 90010);
            // }
            $fund_bill_check = $user_wallet_bill_obj->where($map)->sum('money_deal');

            if (bccomp($money_deals, $fund_bill_check, 2) != 0 || $money_deals < $cash) {
                DB::rollback();
                throw new ApiException('提现金额错误,请联系客服', 90015);
            }
            // 生成提现记录
            $cash_service = Config::where('key', 'cash_service')->value('value');
            $bank_name = Bank::where('id', $bank_info->bank)->value('title');
            $withdraw_no = 'd' . date('ymdHis') . mt_rand(10, 99);
            $user_cash_record = [
                'withdraw_no' => $withdraw_no,
                'uid' => $uid,
                'bank_name' => $bank_name,
                'account_number' => $bank_info->card_no,
                'realname' => $bank_info->card_name,
                'mobile' => $bank_info->telephone,
                'cash' => $cash,
                'cash_service' => $cash_service,
                'status' => 1,
                'memo' => $request->memo ?? '',
                'ask_for_date' => time(),
                'begin_time' => $last_time,
                'end_time' => $end_time,
            ];
            $cash_res = UserCashRecord::create($user_cash_record);
            $cash_id = $cash_res->id;
            // 更新流水单
            $user_wallet_bill_obj->whereIn('id', $ids)->update(['settlement_id' => $cash_id]);
            $balance = bcsub($money_deals, $cash, 2);
            $cash_bill = [
                'uid' => $uid,
                'source' => 2,
                'source_id' => $cash_id,
                'deal_type' => 2,
                'money_deal' => -$cash,
                'money_total' => $balance,
                'settlement_id' => $cash_id,
                'remark' => '结算余额',
            ];
            $user_wallet_bill_obj->create($cash_bill);
            
            $check_balance = $user_wallet_bill_obj->where('settlement_id', $cash_id)->sum('money_deal');
            if ($check_balance != $balance) {
                DB::rollback();
                throw new ApiException('提现金额错误,请联系客服', 90020);
            }
            // 余额账单
            if ($balance > 0) {
                $settlement_bill = [
                    'uid' => $uid,
                    'source' => 6,
                    'source_id' => $cash_id,
                    'deal_type' => 3,
                    'money_deal' => -$balance,
                    'money_total' => 0,
                    'is_public' => 0,
                    'remark' => '结算',
                    'settlement_id' => $cash_id,
                ];
                $user_wallet_bill_obj->create($settlement_bill);
                $balance_bill = [
                    'uid' => $uid,
                    'source' => 6,
                    'source_id' => $cash_id,
                    'deal_type' => 3,
                    'money_deal' => $balance,
                    'money_total' => $balance,
                    'is_public' => 0,
                    'remark' => '结算余额',
                ];
                $user_wallet_bill_obj->create($balance_bill);
            }

            DB::commit();

            $user_wallet_bill_obj->statisticBalance($uid, true);

            // 分发提现任务
            $cash_info = [
                'uid' => $uid,
                'cash_id' => $cash_id,
            ];
            DepositJob::dispatch($cash_info)->delay(Carbon::now()->addSeconds(5));

        } catch (\Exception $e) {
            $code = is_integer($e->getCode()) ? $e->getCode() : 90900;
            throw new ApiException($e->getMessage(), $code);
        }

        return apiReturn(0);
    }

    /**
     * 测试发起分红
     */
    public function launchBonus()
    {
        UserStockRecord::bonus();
    }
}
