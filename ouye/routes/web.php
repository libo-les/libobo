<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

Route::get('/', function () {
	return view('welcome');
});
Route::prefix('admin')->group(function () {
	//验证码
	Route::get('yanzhen', 'Admin\TestController@yanzhen');
	//系统信息
	Route::get('SystemInfo', 'Admin\TestController@SystemInfo');
	//检查
	Route::any('base/check_priv', "Admin\BaseController@check_priv");
	Route::any('base/Futext', "Admin\BaseController@Futext");

	//登录
	Route::any('login/index', "Admin\LoginController@index");
	Route::any('login/logOut', "Admin\LoginController@logOut");
	//头部信息
	Route::get('login/headnews', "Admin\LoginController@headnews");
	Route::post('login/modifyCipher', 'Admin\LoginController@modifyCipher');

	//管理员
	Route::post('admin/addAdmin', 'Admin\AdminController@addAdmin');
	Route::post('admin/delAdmin', 'Admin\AdminController@delAdmin');
	Route::post('admin/index', 'Admin\AdminController@index');
	Route::post('admin/updAdmin', 'Admin\AdminController@updAdmin');
	Route::get('admin/rolelist', 'Admin\AdminController@rolelist');
	Route::post('admin/adminDetail', 'Admin\AdminController@adminDetail');
	Route::post('admin/updPassword', 'Admin\AdminController@updPassword');
	Route::get('admin/adminLog', 'Admin\AdminController@adminLog');

	//用户
	Route::post('user/index', 'Admin\UserController@index');
	Route::get('user/UserExport', 'Admin\UserController@UserExport');

	//角色
	Route::post('admingroup/index', 'Admin\AdminGroupController@index');
	Route::post('admingroup/addGroup', 'Admin\AdminGroupController@addGroup');
	Route::any('admingroup/groupDetail', 'Admin\AdminGroupController@groupDetail');
	Route::post('admingroup/delGroup', 'Admin\AdminGroupController@delGroup');
	Route::post('admingroup/groupMessage', 'Admin\AdminGroupController@groupMessage');
	Route::post('admingroup/updGroup', 'Admin\AdminGroupController@updGroup');

	//权限
	Route::post('module/index', 'Admin\ModuleController@index');
	Route::post('module/addAuthority', 'Admin\ModuleController@addAuthority');
	Route::post('module/authorityDetail', 'Admin\ModuleController@authorityDetail');
	Route::post('module/updauthority', 'Admin\ModuleController@updauthority');
	Route::post('module/delauthority', 'Admin\ModuleController@delauthority');
	Route::post('module/authorityGroup', 'Admin\ModuleController@authorityGroup');

	//商品
	Route::post('goods/index', 'Admin\GoodsController@index');
	Route::post('goods/addgood', 'Admin\GoodsController@addgood');
	Route::post('goods/updgood', 'Admin\GoodsController@updgood');
	Route::post('goods/delgood', 'Admin\GoodsController@delgood');
	Route::post('goods/goodsDetails', 'Admin\GoodsController@goodsDetails');
	Route::any('goods/image', 'Admin\GoodsController@image');
	Route::post('goods/delfutu', 'Admin\GoodsController@delfutu');
	Route::post('goods/isState', 'Admin\GoodsController@isState');

	//商品分类
	Route::post('goodscategory/index', 'Admin\GoodsCategoryController@index');
	Route::post('goodscategory/addcategory', 'Admin\GoodsCategoryController@addcategory');
	Route::post('goodscategory/updcategory', 'Admin\GoodsCategoryController@updcategory');
	Route::post('goodscategory/delcategory', 'Admin\GoodsCategoryController@delcategory');
	Route::post('goodscategory/details', 'Admin\GoodsCategoryController@details');
	Route::post('goodscategory/downcategory', 'Admin\GoodsCategoryController@downcategory');
	//任务
	Route::post('task/index', 'Admin\TaskController@index');
	Route::post('task/addTask', 'Admin\TaskController@addTask');
	Route::post('task/updTask', 'Admin\TaskController@updTask');
	Route::post('task/delTask', 'Admin\TaskController@delTask');
	Route::post('task/taskDetails', 'Admin\TaskController@taskDetails');
	//审核任务
	Route::post('taskusercheck/index', 'Admin\TaskUserCheckController@index');
	Route::post('taskusercheck/taskCheckDetail', 'Admin\TaskUserCheckController@taskCheckDetail');
	Route::post('taskusercheck/examineSpecTask', 'Admin\TaskUserCheckController@examineSpecTask');
	//特殊任务
	Route::post('spectask/index', 'Admin\SpecTaskController@index');
	Route::post('spectask/addSpecTask', 'Admin\SpecTaskController@addSpecTask');
	Route::post('spectask/updSpecTask', 'Admin\SpecTaskController@updSpecTask');
	Route::post('spectask/delSpecTask', 'Admin\SpecTaskController@delSpecTask');
	Route::post('spectask/specTaskDetails', 'Admin\SpecTaskController@specTaskDetails');

	//订单
	Route::post('order/index', "Admin\OrderController@index");
	Route::post('order/deliverGoods', "Admin\OrderController@deliverGoods");
	Route::post('order/delorder', "Admin\OrderController@delorder");
	Route::post('order/orderDetails', "Admin\OrderController@orderDetails");
	Route::post('order/refund', "Admin\OrderController@refund");
	Route::post('order/refuseOrder', "Admin\OrderController@refuseOrder");
	Route::post('order/takeGooes', "Admin\OrderController@takeGooes");
	Route::post('order/isDetermine', "Admin\OrderController@isDetermine");
	Route::get('order/wechatRetrun', "Admin\OrderController@wechatRetrun");
	Route::get('order/orderExport', "Admin\OrderController@orderExport");

	//仓库订单
	Route::post('userstockorder/index', "Admin\UserStockOrderController@index");
	Route::post('userstockorder/stockOrderDetail', "Admin\UserStockOrderController@stockOrderDetail");
	Route::get('userstockorder/stockOrderExport', "Admin\UserStockOrderController@stockOrderExport");

	//广告
	Route::post('platformadv/index', "Admin\PlatformAdvController@index");
	Route::post('platformadv/addplat', "Admin\PlatformAdvController@addplat");
	Route::post('platformadv/updflat', "Admin\PlatformAdvController@updflat");
	Route::post('platformadv/delfalt', "Admin\PlatformAdvController@delfalt");
	Route::post('platformadv/faltDetail', "Admin\PlatformAdvController@faltDetail");
	Route::post('platformadv/faltGoods', "Admin\PlatformAdvController@faltGoods");
	Route::post('platformadv/faltTask', "Admin\PlatformAdvController@faltTask");
	Route::post('platformadv/setSort', "Admin\PlatformAdvController@setSort");
	//广告位
	Route::post('position/index', "Admin\PositionController@index");
	Route::post('position/addposition', "Admin\PositionController@addposition");
	Route::post('position/updposition', "Admin\PositionController@updposition");
	Route::post('position/delposition', "Admin\PositionController@delposition");
	Route::post('position/positionDetails', "Admin\PositionController@positionDetails");
	Route::get('position/positionDown', "Admin\PositionController@positionDown");
	//广告链接
	Route::post('platfotmadvurl/index', "Admin\PlatfotmAdvUrlController@index");
	Route::post('platfotmadvurl/addPlatLink', "Admin\PlatfotmAdvUrlController@addPlatLink");
	Route::post('platfotmadvurl/delPlatLink', "Admin\PlatfotmAdvUrlController@delPlatLink");
	Route::post('platfotmadvurl/platLinkDetail', "Admin\PlatfotmAdvUrlController@platLinkDetail");
	Route::post('platfotmadvurl/updPlatLink', "Admin\PlatfotmAdvUrlController@updPlatLink");
	Route::post('platfotmadvurl/platLinkDown', "Admin\PlatfotmAdvUrlController@platLinkDown");
	//首页
	Route::any('home/index', "Admin\HomeController@index");

	//代言人
	Route::post('spokesman/index', "Admin\SpokesmanController@index");
	Route::post('spokesman/examine', "Admin\SpokesmanController@examine");
	Route::post('spokesman/stayexamine', "Admin\SpokesmanController@stayexamine");
	Route::get('spokesman/stock', "Admin\SpokesmanController@stock");
	Route::get('spokesman/spokExport', "Admin\SpokesmanController@spokExport");

	//代言人条件
	Route::get('spokemanCondition/index', "Admin\SpokemanConditionController@index");
	Route::post('spokemanCondition/addSpokCondit', "Admin\SpokemanConditionController@addSpokCondit");

	//代理商
	Route::post('agent/index', "Admin\AgentController@index");
	Route::post('agent/agentExamine', "Admin\AgentController@agentExamine");
	Route::post('agent/delAgent', "Admin\AgentController@delAgent");
	Route::post('agent/stayAgent', "Admin\AgentController@stayAgent");
	Route::post('agent/agentDetail', "Admin\AgentController@agentDetail");
	Route::get('agent/setdetail', "Admin\AgentController@setdetail");
	Route::post('agent/updset', "Admin\AgentController@updset");
	Route::get('agent/agentExport', "Admin\AgentController@agentExport");
	Route::get('agent/QRcode', "Admin\AgentController@QRcode");

	//代理商一级分享任务
	Route::post('AgentTask/index', 'Admin\AgentTaskController@index');
	Route::post('AgentTask/addTask', 'Admin\AgentTaskController@addTask');
	Route::post('AgentTask/updTask', 'Admin\AgentTaskController@updTask');
	Route::post('AgentTask/delTask', 'Admin\AgentTaskController@delTask');
	Route::post('AgentTask/taskDetails', 'Admin\AgentTaskController@taskDetails');
	//代理商一级特殊任务
	Route::post('AgentSpectaks/index', 'Admin\AgentSpectaksController@index');
	Route::post('AgentSpectaks/addSpecTask', 'Admin\AgentSpectaksController@addSpecTask');
	Route::post('AgentSpectaks/updSpecTask', 'Admin\AgentSpectaksController@updSpecTask');
	Route::post('AgentSpectaks/delSpecTask', 'Admin\AgentSpectaksController@delSpecTask');
	Route::post('AgentSpectaks/specTaskDetails', 'Admin\AgentSpectaksController@specTaskDetails');
	//设置
	Route::get('setup/delivery', "Admin\SetupController@delivery");
	Route::post('setup/updStore', "Admin\SetupController@updStore");
	Route::get('setup/SystemInfo', "Admin\SetupController@SystemInfo");

	//代理等级
	Route::post('itnature/index', "Admin\ItnatureController@index");
	Route::post('itnature/addcondition', "Admin\ItnatureController@addcondition");
	Route::post('itnature/updcondition', "Admin\ItnatureController@updcondition");
	Route::post('itnature/delcondition', "Admin\ItnatureController@delcondition");
	Route::post('itnature/conditionDetil', "Admin\ItnatureController@conditionDetil");
	//提现记录
	Route::post('usecashrecord/index', "Admin\UserCashRecordController@index");

	//配送规格
	Route::get('specs/index', "Admin\SpecsController@index");
	//品牌
	Route::post('brand/index', "Admin\BrandController@index");
	Route::post('brand/addbrand', "Admin\BrandController@addbrand");
	Route::post('brand/updBrand', "Admin\BrandController@updBrand");
	Route::post('brand/delBrand', "Admin\BrandController@delBrand");
	Route::post('brand/brandDetail', "Admin\BrandController@brandDetail");
	Route::get('brand/dBrand', "Admin\BrandController@dBrand");
	//省市二级联动
	Route::post('region/index', "Admin\RegionController@index");
	//图片空间
	Route::any('picture/index', "Admin\PictureController@index");

});

Route::get('excel/export', 'ExcelController@export');
Route::get('excel/import', 'ExcelController@import');