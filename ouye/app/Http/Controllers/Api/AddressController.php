<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

use App\Http\Requests\Api\VerifyAddress;

use App\Http\Models\UserAddress;

class AddressController extends Controller
{
    /**
     * 全部收货地址
     */
    public function index(Request $request)
    {
        $uid = $request->token_info['uid'];
        $address_info = UserAddress::where('uid', $uid)->get();
        return apiReturn(0, 'ok', $address_info);
    }

    /**
     * 添加收货地址
     */
    public function add(VerifyAddress $request)
    {
        $data = $request->all();
        $uid = $request->token_info['uid'];

        $address_obj = new UserAddress;
        if ($request->is_default == 1) {
            $address_obj->where('uid', $uid)->update(['is_default' => 0]);
        }
        $data['uid'] = $uid;

        $result = $address_obj->create($data);
        
        return apiReturn(0, 'ok', $result);
    }
    /**
     * 查看地址
     */
    public function show(Request $request)
    {
        $addr_id = $request->addr_id;
        $uid = $request->token_info['uid'];

        $address_info = UserAddress::where('uid', $uid)->where('id', $addr_id)->first();

        return apiReturn(0, 'ok', $address_info);
    }

    /**
     * 删除地址
     */
    public function remove(Request $request)
    {
        $addr_id = $request->addr_id;

        $result = UserAddress::where('id', $addr_id)->delete();

        return apiReturn(0, 'ok', $result);
    }
    /**
     * 修改地址
     */
    public function edit(VerifyAddress $request)
    {
        if (empty($addr_id = $request->addr_id)) {
            return apiReturn(1);
        }
        $uid = $request->token_info['uid'];
        
        if ($request->is_default == 1) {
            UserAddress::where('uid', $uid)->update(['is_default' => 0]);
        }

        $result = UserAddress::where('id', $addr_id)->update($request->except(['addr_id', 'token_info']));

        return apiReturn(0, 'ok', $result);
    }
    /**
     * 修改默认地址
     */
    public function editDefault(Request $request)
    {
        if (empty($addr_id = $request->addr_id)) {
            return apiReturn(1);
        }
        $uid = $request->token_info['uid'];
        UserAddress::where('uid', $uid)->update(['is_default' => 0]);
        $result = UserAddress::where('uid', $uid)->where('id', $addr_id)->update(['is_default' => 1]);

        return apiReturn(0, 'ok', $result);
    }
}
