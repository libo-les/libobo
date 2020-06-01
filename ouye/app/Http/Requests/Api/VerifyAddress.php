<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class VerifyAddress extends FormRequest
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
            'consignee' => 'required',
            'mobile' => 'required',
            'region' => 'required',
            'province' => 'required',
            'city' => 'required',
            'district' => 'required',
            'detailed_address' => 'required',
            'is_default' => 'required',
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
            'consignee.required' => '收货人姓名必填',
            'mobile.required' => '电话必填',
            'region.required' => '地址必选',
            'province.required' => '详情地址必填',
            'city.required' => '详情地址必填',
            'district.required' => '详情地址必填',
            'detailed_address.required' => '详情地址必填',
            'is_default.required' => '是否默认选中',
		];
    }
}
