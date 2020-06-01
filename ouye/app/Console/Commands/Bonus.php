<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Http\Models\User;
use App\Http\Models\Config;
use App\Http\Models\UserAwardRecord;

/**
 * 分红
 */
class Bonus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:bonus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The daily bonus';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $bonus_scale = Config::where('key', 'bonus_scale')->value('value');
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
                    $stock_infos = DB::table('cs_user_stock as s')
                        ->selectRaw('sum(s.stock_num * g.promotion_price) as total_stock_monay')
                        ->join('cs_goods as g', 's.goods_id', '=', 'g.id')
                        ->where('s.stock_num', '>', 0)
                        ->where('s.uid', $uid)
                        ->first();
                    $award = $bonus_scale * $stock_infos['total_stock_monay'] / 100;
                    // DB::enableQueryLog();
                    $is_award = UserAwardRecord::where('uid', $uid)->where('source', 1)->WhereBetween('created_at', [strtotime('today'), strtotime('tomorrow')])->count();
                    // var_dump(DB::getQueryLog());
                    if ($is_award) {
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
