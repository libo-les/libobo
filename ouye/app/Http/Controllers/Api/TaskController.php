<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Models\Task;
use Illuminate\Support\Facades\DB;
use App\Http\Models\User;
use App\Http\Models\TaskUserShareRecord;
use App\Http\Models\UserAwardRecord;
use App\Http\Models\TaskUserCheck;
use App\Http\Models\Config;
use Illuminate\Support\Facades\Storage;
use App\Http\Models\TaskExclusive;
use App\Http\Models\UserWalletBill;
use App\Http\Models\Api\Agent;

/**
 * 任务
 */
class TaskController extends Controller
{
    /**
     * 任务列表
     */
    public function index(Request $request)
    {
        $uid = $request->token_info['uid'];
        $user_type = User::where('id', $uid)->value('user_type');

        $lists = Task::where('is_valid', 1)->whereRaw("JSON_CONTAINS(role_type, '[{$user_type}]')")->paginate(20);

        $img_url = config('app.img_url');
        foreach ($lists as $key => $list) {
            $lists[$key]->small_img = $img_url . '/' . $list->small_img;
            $lists[$key]->img = $img_url . '/' . $list->img;
        }

        return apiReturn(0, 'ok', compact('lists'));
    }

    /**
     * 任务详情
     */
    public function detail(Request $request)
    {
        if (!$task_id = $request->task_id) {
            return apiReturn(20000);
        }
        $uid = $request->token_info['uid'];

        $task_info = Task::where('id', $task_id)->first();
        if (empty($task_info)) {
            return apiReturn(20001);
        }

        $img_url = config('app.img_url');
        $task_info->content = str_replace('src="', 'src="' . $img_url, htmlspecialchars_decode($task_info->content));


        if (in_array(4, $task_info->role_type)) {
            $exclusive_info = TaskExclusive::where([
                'uid' => $uid,
                'task_id' => $task_id
            ])->first();
        }
        if ($task_info->type == 2) {
            $task_info->task_check_id = TaskUserCheck::where([
                'uid' => $uid,
                'task_id' => $task_id,
                'status' => 1,
            ])
                ->whereNull('user_delete_at')
                ->value('id');
            $task_info->already_num = TaskUserCheck::where([
                'uid' => $uid,
                'task_id' => $task_id,
            ])
                ->whereIn('status', [2, 3, 4])
                ->count();
        }

        $store_name = Config::where('key', 'store_name')->value('value');

        // 生产分享码
        $scode = encrypt([
            'uid' => $uid,
        ]);

        return apiReturn(0, 'ok', compact('task_info', 'store_name', 'scode', 'exclusive_info'));
    }

    /**
     *  分享获奖励
     */
    public function shareAWard(Request $request)
    {
        if (!$scode = $request->scode) {
            return apiReturn(20000);
        }
        if (!$task_id = $request->task_id) {
            return apiReturn(20000);
        }
        $uid = $request->token_info['uid'] ?? 0;
        $uaid = $request->token_info['uaid'] ?? 0;

        $scode = decrypt($request->scode);
        $suid = $scode['uid'];
        $user_info = User::where('id', $suid)->where('user_type', '>', 1)->first();
        
        if (empty($user_info)) {
            return apiReturn(90001, '该用户不能获得奖励');
        }
        // 一个用户奖励一次
        $award_history = TaskUserShareRecord::where([
            'suid' => $suid,
            'task_id' => $task_id,
        ])
            ->where(function ($query) use ($uid, $uaid) {
                $query->orWhere('uid', $uid)
                    ->orWhere('uaid', $uaid);
            })
            ->count();
        if ($award_history) {
            return apiReturn(90002, '已奖励');
        }

        $task_info = Task::where('id', $task_id)->where('type', 1)->first();
        if (empty($task_info)) {
            return apiReturn(1, '奖励失败');
        }
        // 次数限制
        if (in_array(4, $task_info->role_type)) {
            $exclusive_info = TaskExclusive::where([
                'uid' => $suid,
                'task_id' => $task_id,
                ['surplus_num', '>', 0]
            ])->first();
            if (empty($exclusive_info)) {
                return apiReturn(1, '您的专属任务完成次数已达上限');
            }
            $exclusive_info->decrement('surplus_num');
            $exclusive_info->save();
        } else {
            $share_num = TaskUserShareRecord::where('suid', $suid)->where('task_id', $task_id)->count();
            if ($share_num > $task_info->allow_num) {
                return apiReturn(90003, '奖励超出最大值');
            }
        }
        // 添加奖励
        $share_record = [
            'uid' => $uid,
            'uaid' => $uaid,
            'suid' => $suid,
            'task_id' => $task_id,
            'award' => $task_info->award,
        ];
        $share_record_res = TaskUserShareRecord::create($share_record);
        $award_record = [
            'uid' => $suid,
            'source' => 4,
            'source_id' => $share_record_res['id'],
            'source_user_id' => $uid,
            'is_bill' => 0,
            'award' => $task_info->award,
        ];
        $award_info = UserAwardRecord::create($award_record);
        if (in_array(4, $task_info->role_type)) {
            (new UserWalletBill)->addAwardBill($award_info);

            Agent::where('uid', $suid)->increment('task_award', $task_info->award);
        }

        return apiReturn();
    }

    /**
     * 领取任务
     */
    public function takeTask(Request $request)
    {
        if (!$task_id = $request->task_id) {
            return apiReturn(20000);
        }
        $uid = $request->token_info['uid'];

        $task_check_id = TaskUserCheck::where([
            'uid' => $uid,
            'task_id' => $task_id,
            'status' => 1,
        ])
            ->whereNull('user_delete_at')
            ->value('id');
        if ($task_check_id) {
            return apiReturn(1, '已经认领', ['task_check_id' => $task_check_id]);
        }

        $task_info = Task::where('id', $task_id)->where('type', 2)->first();
        if (empty($task_info)) {
            return apiReturn(1, '领取失败');
        }
        // 次数限制
        if (in_array(4, $task_info->role_type)) {
            $exclusive_info = TaskExclusive::where([
                'uid' => $uid,
                'task_id' => $task_id,
                ['surplus_num', '>', 0]
            ])->first();
            if (empty($exclusive_info)) {
                return apiReturn(1, '您的专属任务已经完成');
            }
            $exclusive_info->decrement('surplus_num');
            $exclusive_info->save();
        } else {
            $task_complete_num = TaskUserCheck::where([
                'uid' => $uid,
                'task_id' => $task_id,
            ])
                ->count();
            if ($task_complete_num >= $task_info->allow_num) {
                return apiReturn(1, '达到任务次数上限');
            }
        }

        $check_info = [
            'uid' => $uid,
            'task_id' => $task_id,
            'status' => 1,
        ];

        $task_check_res = TaskUserCheck::create($check_info);

        $task_check_id = $task_check_res->id;

        return apiReturn(0, 'ok', compact('task_check_id'));
    }
    /**
     * 删除认领任务
     */
    public function removeTake(Request $request)
    {
        if (!$task_check_id = $request->task_check_id) {
            return apiReturn(20000);
        }
        $uid = $request->token_info['uid'];

        TaskUserCheck::where([
            'id' => $task_check_id,
            'uid' => $uid,
        ])->update([
            'user_delete_at' => time()
        ]);

        return apiReturn(0);
    }

    /**
     * 提交任务凭证
     */
    public function subCertificate(Request $request)
    {
        if (!$task_check_id = $request->task_check_id) {
            return apiReturn(20000);
        }
        $uid = $request->token_info['uid'];

        $task_record = [
            'status' => 2,
            'content' => $request->content,
            'images' => $request->images,
        ];

        $record_res = TaskUserCheck::where([
            'id' => $task_check_id,
            'uid' => $uid,
        ])
            ->whereIn('status', [1, 2, 4])
            ->whereNull('user_delete_at')
            ->update($task_record);

        return $record_res ? apiReturn(0) : apiReturn(1);
    }

    /**
     * 删除任务图片
     */
    public function delTaskImg(Request $request)
    {
        if (!$images = $request->images) {
            return apiReturn(20000);
        }
        if (!$task_check_id = $request->task_check_id) {
            return apiReturn(20000);
        }
        $uid = $request->token_info['uid'];

        $images = json_decode($images);
        foreach ($images as $key => $image) {
            $check_num = TaskUserCheck::where([
                'id' => $task_check_id,
                'uid' => $uid,
            ])
                ->whereIn('status', [1, 2, 4])
                ->whereRaw("JSON_CONTAINS(images, '[\"{$image}\"]')")
                ->count();

            if ($check_num == 0) {
                return apiReturn(1, "信息错误");
            }

            $path = str_replace('storage/', '', $image);

            $exists = Storage::exists($path);
            if (!$exists) {
                return apiReturn(1, "信息错误");
            }
            $res = Storage::delete($path);
        }

        return $res ? apiReturn() : apiReturn(1);
    }

    /**
     * 我的任务
     */
    public function myTask(Request $request)
    {
        $uid = $request->token_info['uid'];

        $check_list = TaskUserCheck::where([
            'uid' => $uid,
        ])
            ->whereNull('user_delete_at')
            ->paginate();

        $img_url = config('app.img_url');

        foreach ($check_list as $key => $list) {
            $taak_info = Task::where('id', $list->task_id)
                ->select('title', 'description', 'award', 'small_img')
                ->first();
            $taak_info->small_img = $img_url . '/' . $taak_info->small_img;
            $check_list[$key]['taak_info'] = $taak_info;
        }

        return apiReturn(0, 'ok', compact('check_list'));
    }

    /**
     * 我的任务详情
     */
    public function myTaskDetails(Request $request)
    {
        if (!$task_check_id = $request->task_check_id) {
            return apiReturn(20000);
        }
        $uid = $request->token_info['uid'];

        $check_info = TaskUserCheck::where([
            'uid' => $uid,
            'id' => $task_check_id,
        ])
            ->first();

        $task_info = Task::where('id', $check_info->task_id)->first();

        $img_url = config('app.img_url');
        $task_info->content = str_replace('src="', 'src="' . $img_url, htmlspecialchars_decode($task_info->content));

        if (in_array(4, $task_info->role_type)) {
            $exclusive_info = TaskExclusive::where([
                'uid' => $uid,
                'task_id' => $check_info->task_id
            ])->first();
        }

        return apiReturn(0, 'ok', compact('check_info', 'task_info', 'exclusive_info'));
    }

    /**
     * 已提交信息
     */
    public function alreadySubInfo(Request $request)
    {
        if (!$task_check_id = $request->task_check_id) {
            return apiReturn(20000);
        }
        $uid = $request->token_info['uid'];

        $info = TaskUserCheck::where([
            'uid' => $uid,
            'id' => $task_check_id,
        ])
            ->first();

        $domain = config('app.img_url');

        return apiReturn(0, 'ok', compact('info', 'domain'));
    }
}
