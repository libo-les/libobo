<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::middleware('auth:api')->get('/user', function (Request $request) {
	return $request->user();
});
// 小程序接口
Route::post('oauth/getOpenid', "Api\OauthController@getOpenid");
Route::post('index/advDetails', "Api\IndexController@advDetails");
// 回调地址
Route::any('PayBackGoods/goodsPayHandle', "Api\PayBackGoodsController@goodsPayHandle");
Route::any('PayBackGoods/supplementManPayHandle', "Api\PayBackGoodsController@supplementManPayHandle");
Route::any('PayBackGoods/supplementAgentPayHandle', "Api\PayBackGoodsController@supplementAgentPayHandle");

Route::any('index/shopInfo', "Api\IndexController@shopInfo");
Route::post('spokesman/applyCondition', "Api\SpokesmanController@applyCondition");
Route::post('order/editfinishOrdre', "Api\OrderController@editfinishOrdre");
// 测试接口
Route::any('wallet/launchBonus', "Api\WalletController@launchBonus");

// 用户已经授权
Route::group(['middleware' => ['user_auth']], function () {
	Route::post('index', "Api\IndexController@index");
	// 绑定用户
	Route::post('index/smsVerify', "Api\IndexController@smsVerify");
	Route::group(['prefix' => 'userinfo'], function () {
		Route::post('getUserInfo', "Api\UserInfoController@getUserInfo");
		Route::post('setUserInfo', "Api\UserInfoController@setUserInfo");
		Route::post('isUserTel', "Api\UserInfoController@isUserTel");
		Route::post('smsVerify', "Api\UserInfoController@smsVerify");
		Route::post('bindTelephone', "Api\UserInfoController@bindTelephone");
		Route::post('wxAccreditLogin', "Api\UserInfoController@wxAccreditLogin");
	});
	// 商品
	Route::group(['prefix' => 'goods'], function () {
		Route::post('index', "Api\GoodsController@index");
		Route::post('goodsDetail', "Api\GoodsController@goodsDetail");
	});
	// 任务
	Route::post('task/shareAWard', "Api\TaskController@shareAWard");
});

// 已绑定手机
Route::group(['middleware' => ['user_auth', 'bindtel']], function () {
	Route::post('userinfo/isUserTel', "Api\UserInfoController@isUserTel");
	// 商品
	Route::post('goods/createPoster', "Api\GoodsController@createPoster");
	// 购物车
	Route::post('goods/addCart', "Api\GoodsController@addCart");
	Route::post('goods/removeCart', "Api\GoodsController@removeCart");
	Route::post('goods/setCartNum', "Api\GoodsController@setCartNum");
	Route::post('goods/cartList', "Api\GoodsController@cartList");
	// 订单
	Route::post('order/statement', "Api\OrderController@statement");
	Route::post('order/payResult', "Api\OrderController@payResult");
	Route::post('order/createOrder', "Api\OrderController@createOrder");
	Route::post('order/getWxOrderStatus', "Api\OrderController@getWxOrderStatus");
	Route::post('order/cancalPay', "Api\OrderController@cancalPay");
	Route::post('order/againPay', "Api\OrderController@againPay");
	Route::post('order/index', "Api\OrderController@index");
	Route::post('order/detail', "Api\OrderController@detail");
	Route::post('order/confirmReceipt', "Api\OrderController@confirmReceipt");
	Route::post('order/applyRefund', "Api\OrderController@applyRefund");
	Route::post('order/goodsRefund', "Api\OrderController@goodsRefund");
	Route::post('order/delete', "Api\OrderController@delete");
	// 地址
	Route::post('address', "Api\AddressController@index");
	Route::post('address/show', "Api\AddressController@show");
	Route::post('address/add', "Api\AddressController@add");
	Route::post('address/remove', "Api\AddressController@remove");
	Route::post('address/edit', "Api\AddressController@edit");
	Route::post('address/editDefault', "Api\AddressController@editDefault");
	// 代理商
	Route::post('agent/applyCondition', "Api\AgentController@applyCondition");
	Route::post('agent/apply', "Api\AgentController@apply");
	Route::post('agent/index', "Api\AgentController@index");
	Route::post('agent/agentTask', "Api\AgentController@agentTask");
	Route::post('agent/personalCenter', "Api\AgentController@personalCenter");
	Route::post('agent/applyUpgrade', "Api\AgentController@applyUpgrade");
	// 代言人
	Route::post('spokesman/getScode', "Api\SpokesmanController@getScode");
	Route::post('spokesman/conditionComplete', "Api\SpokesmanController@conditionComplete");
	Route::post('spokesman/apply', "Api\SpokesmanController@apply");
	Route::post('spokesman', "Api\SpokesmanController@index");
	Route::post('spokesman/receiveAward', "Api\SpokesmanController@receiveAward");
	Route::post('spokesman/myPartner', "Api\SpokesmanController@myPartner");
	// 库存
	Route::post('stock', "Api\StockController@index");
	Route::post('stock/replenishList', "Api\StockController@replenishList");
	Route::post('stock/stockStatement', "Api\StockController@stockStatement");
	Route::post('stock/createOrderMan', "Api\StockController@createOrderMan");
	Route::post('stock/createOrderAgent', "Api\StockController@createOrderAgent");
	Route::post('stock/cancelSupplementPay', "Api\StockController@cancelSupplementPay");
	Route::post('stock/payResult', "Api\StockController@payResult");
	Route::post('stock/getWxStockOrderStatus', "Api\StockController@getWxStockOrderStatus");
	Route::post('stock/againPay', "Api\StockController@againPay");
	Route::post('stock/orderlise', "Api\StockController@orderLise");
	Route::post('stock/orderDetail', "Api\StockController@orderDetail");
	Route::post('stock/goodsStock', "Api\StockController@goodsStock");
	Route::post('stock/pickGoods', "Api\StockController@pickGoods");
	Route::post('stock/delStockOrder', "Api\StockController@delStockOrder");
	// 余额
	Route::post('wallet/balance', "Api\WalletController@balance");
	Route::post('wallet/record', "Api\WalletController@record");
	Route::post('wallet/depositStatement', "Api\WalletController@depositStatement");
	Route::post('wallet/selectBank', "Api\WalletController@selectBank");
	Route::post('wallet/addCard', "Api\WalletController@addCard");
	Route::post('wallet/unbindCard', "Api\WalletController@unbindCard");
	Route::post('wallet/userBank', "Api\WalletController@userBank");
	Route::post('wallet/setDefault', "Api\WalletController@setDefault");
	Route::post('wallet/deposit', "Api\WalletController@deposit");
	// 任务
	Route::post('task', "Api\TaskController@index");
	Route::post('task/detail', "Api\TaskController@detail");
	Route::post('task/takeTask', "Api\TaskController@takeTask");
	Route::post('task/removeTake', "Api\TaskController@removeTake");
	Route::post('task/subCertificate', "Api\TaskController@subCertificate");
	Route::post('task/delTaskImg', "Api\TaskController@delTaskImg");
	Route::post('task/myTask', "Api\TaskController@myTask");
	Route::post('task/myTaskDetails', "Api\TaskController@myTaskDetails");
	Route::post('task/alreadySubInfo', "Api\TaskController@alreadySubInfo");
	// 上传
	Route::post('index/uploadFile', "Api\IndexController@uploadFile");
});
