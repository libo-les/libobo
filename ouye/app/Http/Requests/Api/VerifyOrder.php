<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOrder extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'buy_goods' => 'required',
            'total_cash' => 'required',
            'deliver_fee' => 'required',
            'addr_id' => 'required',
        ];
    }
    /**
     * 获取已定义的验证规则的错误消息。
     *
     * @return array
     */
    public function messages()
    {
        return [
            'buy_goods.required' => '请购买商品1',
            'total_cash.required' => '请购买商品',
            'deliver_fee.required' => '请选择配送地址',
            'addr_id.required' => '请选择配送地址',
        ];
    }

}
