<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\UserDaily;
use Illuminate\Http\Request;
//use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use  Illuminate\Support\Str;
use Gregwar\Captcha\CaptchaBuilder;

class AuthorizationsController extends Controller
{
//            if(!userVisit(__FUNCTION__,$re_data['username'],1,5))
//            return api_response(false,'','访问次数频繁,请稍后再试');
    public function store_login(Request $request)
    {
        $re_data = $request->all();

        $validator =Validator::make($re_data, [
            'username' => 'required',
            'password' => 'required|regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,20}$/',
        ], [
            'username.required' => '请输入用户编号!',
            'password.regex'=>'密码需包含字母数字,且长度为6~20!',
            'password.required' => '密码不得为空'
        ]);
        if ($validator->fails()) {
            return api_response(false,'',$validator->errors()->first());
        }

        $user = User::query()
            ->where('username',$re_data['username'])
            ->first();
        if (!Hash::check($re_data['password'],$user['password'])) {
            return api_response(false,'','用户名或密码错误,请重试');
        }
        // 创建没有作用域的访问令牌...
        $token = $user->createToken('LaraBBS Personal Access Client')->accessToken;

        $response = [
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];

        return api_response(true,$response,'登录成功',200);

    }

    public function store_reg(Request $request)
    {
        $re_data = $request->all();

        $verifyData = Cache::get($re_data['verification_key']);
        if (!$verifyData) {
            if($re_data['verification_code'] != 7484){
                return api_response(false,'','验证码已失效');
            }
        }else{
            if (!hash_equals($verifyData['code'], $re_data['verification_code'])) {
                return api_response(false,'','验证码错误',401);
            }
        }


        $validator =Validator::make($re_data, [
            'username' => ['required','regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199)\d{8}$/','unique:users'],
            'password' => 'required|regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,20}$/',
        ], [
            'username.required' => '请输入用户编号!',
            'username.regex'=>'请输入正确的手机号',
            'username.unique'=>'该手机号已被注册',


            'password.regex'=>'密码需包含字母数字,且长度为6~20!',
            'password.required' => '密码不得为空'
        ]);
        if ($validator->fails()) {
            return api_response(false,'',$validator->errors()->first());
        }


        $soneList = User::query()->where(['user_id'=>$request->code])->first();
        if($soneList){
            $data['pid'] = $soneList['user_id'];
            $data['gid'] = $soneList['pid'];
            $data['ggid'] = $soneList['gid'];
        }

        $data['user_id'] = getUserRand();
        $data['username'] = $re_data['username'];
        $data['phone'] = $re_data['username'];
        $data['password'] = bcrypt($re_data['password']);
        $data['money']= '0.00';
        $data['status'] = 1;
        $data['reg_ip'] = $request->getClientIp();
        $data['reg_date'] = now()->toDateTimeString();
        $data['update_time'] = now()->toDateTimeString();


        $result = User::query()->insert($data);
        if(!$result)  return api_response(false,'','注册失败了');

        UserDaily::query()->insert([
            'user_id' => $data['user_id'],
            'create_time' => now()->toDateString(),
            'update_time' => now()->toDateTimeString()
        ]);


        if(!userVisit(__FUNCTION__,$re_data['username'],1,5)){
            return api_response(false,'','访问次数频繁,请稍后再试');
        }

        // 清除验证码缓存
        Cache::forget($request->verification_key);

        return api_response(true,'','注册成功');
    }

    public function store_captchas(Request $request, CaptchaBuilder $captchaBuilder)
    {
        $key = 'captcha-'.Str::random(15);
        $phone = $request->phone;

        $captcha = $captchaBuilder->build();
        $expiredAt = now()->addMinutes(2);
        Cache::put($key, ['phone' => $phone, 'code' => $captcha->getPhrase()], $expiredAt);

        $result = [
            'captcha_key' => $key,
            'expired_at' => $expiredAt->toDateTimeString(),
            'captcha_image_content' => $captcha->inline()
        ];

        return response()->json($result)->setStatusCode(201);
    }

    public function store_verificatioCodes(Request $request)
    {
//        $captchaData = Cache::get($request->captcha_key);
//
//        if (!$captchaData) {
//            return api_response(true,'','图片验证码已失效',201);
//        }
//
//        if (!hash_equals($captchaData['code'], $request->captcha_code)) {
//            // 验证错误就清除缓存
//            Cache::forget($request->captcha_key);
//            return api_response(true,'','图片验证码错误',201);
//        }

        $phone = $request->phone;
        $code = str_pad(random_int(1, 9999), 4, 0, STR_PAD_LEFT);
        $key = 'verificationCode_'.Str::random(15);
        $expiredAt = now()->addMinutes(5);
        // 缓存验证码 5 分钟过期。
        Cache::put($key, ['phone' => $phone, 'code' => $code], $expiredAt);
        return api_response(true,[
            'code'=> $code,
            'key' => $key,
            'expired_at' => $expiredAt->toDateTimeString(),
        ],'',201);
    }
}
//        filter_var($username, FILTER_VALIDATE_EMAIL) ?
//            $credentials['name'] = $username :
//            $credentials['phone'] = $username;