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


// Route::prefix('v2')->name('api.v2.')->group(function() {
//     Route::get('version', function() {
//         return 'this is version v2';
//     })->name('version');
// });

 // 登录后可以访问的接口
//prefix('v1')->
//->name('api.v1.')
//->middleware('throttle:'.config('api.rate_limits.sign'))

Route::namespace('Api')->group(function() {

    Route::post('verificationCodes', 'AuthorizationsController@store_verificatioCodes')->name('authorizations.store');// 短信验证码
    Route::post('store_reg', 'AuthorizationsController@store_reg')->name('authorizations.store_reg');// 用户注册
    Route::post('store_login', 'AuthorizationsController@store_login')->name('authorizations.store_login');// 用户登录
    Route::post('captchas', 'AuthorizationsController@store_captchas')->name('authorizations.store');// 图片验证码

    Route::middleware('auth:api')->group(function() {
        Route::post('home', 'HomeController@homepage')->name('user.home');// 首页

    });

});

