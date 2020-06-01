<?php
namespace App\Http\Controllers\Admin;
use Captcha;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query;
use Intervention\Image\ImageManager;

/**
 * 
 */
class TestController extends Controller
{
	/**
	 * [yanzhen 验证码生成]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function yanzhen(Request $request)
	{	
		return apiReturn(0,'验证码', Captcha::create('default',true));
		
	}
	


}




?>