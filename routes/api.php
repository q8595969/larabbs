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

Route::prefix('v1')->namespace('Api')->name('api.v1.')->middleware('throttle:'.config('api.rate_limits.sign'))->group(function() {
    Route::get('version', function() {
        return 'this is version v1';
    })->name('version');

    // 短信验证码
    Route::post('verificationCodes', 'VerificationCodesController@store')
        ->name('verificationCodes.store');

    // 用户注册
    Route::post('users', 'UsersController@store')
        ->name('users.store');

    // 登录
    Route::post('authorizations', 'AuthorizationsController@store')
        ->name('api.authorizations.store');

    // 刷新token
    Route::put('authorizations/current', 'AuthorizationsController@update')
        ->name('authorizations.update');
    // 删除token
    Route::delete('authorizations/current', 'AuthorizationsController@destroy')
        ->name('authorizations.destroy');
});