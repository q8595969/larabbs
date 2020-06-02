<?php

function route_class()
{
    return str_replace('.', '-', Route::currentRouteName());
}

function api_response($status = true, $json_data = '', $message = '', $rspCode = '200')
{
    return json_encode(array('success'=>$status, 'data'=>$json_data, 'message'=>$message, 'code'=>$rspCode));
}

//截取
if (!function_exists("filter_money")) {
    function filter_money($money,$accuracy=4)
    {
        $str_ret = 0;
        if (empty($money) === false) {
            $str_ret = sprintf("%.".$accuracy."f", substr(sprintf("%.".($accuracy+1)."f", floatval($money)), 0, -1));
        }

        return floatval($str_ret);
    }
}
//2位数 向上取整
if (!function_exists("filter_money_pro")) {
    function filter_money_pro($money)
    {
        $str_ret = ceil($money * 10000 / 100) / 100;
        return floatval($str_ret);
    }
}

if (!function_exists("userVisit")) {
    function userVisit($func,$user,$num,$second){
        //方法名 用户 几次 几秒钟
        if(\Cache::get($func.$user)){
            //5分钟之内的话 还可以访问2次
            $count = \Cache::get($func.$user);
            if($count<$num){
                \Cache::increment($func.$user);
                return true;
            }else{
                return false;
            }     }else{
            //添加5分钟缓存 和 访问次数
            \Cache::put($func.$user,1,$second);
            return true;
        }
    }
}

//创建新的6位用户ID
if (!function_exists("getUserRand")) {
    function getUserRand()
    {
        $rand_number = null;
        for ($j = 0; $j < 6; $j++) {
            if ($j == 0) {
                $rand_number = rand(1, 9);
            } else {
                $rand_number = $rand_number . rand(0, 9);
            }
        }
        $flag = \App\Models\User::query()->where('user_id', $rand_number)->first();
        if ($flag) {
            $rand_number = getUserRand();
        }
        return $rand_number;
    }
}