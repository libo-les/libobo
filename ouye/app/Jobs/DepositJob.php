<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use App\Http\Models\UserCashRecord;

class DepositJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $param;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($param)
    {
        $this->param = $param;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $uid = intval($this->param['uid']); 
        $cash_id = intval($this->param['cash_id']);
        if (empty($uid) || empty($cash_id)) {
            return;
        }
        Log::useFiles(storage_path('log/use_deposit/' . date('ymd') . 'user' . $this->param['uid'] . '.log'));
        Log::info('开始处理提现：' . json_encode($this->param));

        $cash_info = UserCashRecord::where('uid', $uid)->where('id', $cash_id)->first();
        if (!$cash_info) {
            Log::info('数据错误');
        } elseif ($cash_info->status > 1) {
            return; 
        }
        // 先锁定该提现记录，防止重复提现
        $cash_info->status = 2;
        $cash_info->save();

        $cash_info->status = 4;
        $cash_info->save();

    }
}
