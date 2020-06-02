<?php

namespace App\Http\Controllers\Api;

use App\Models\Banner;
use App\Models\User;
use App\Models\UserDaily;
use App\Models\UserRob;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class HomeController extends Controller
{

    public $user_id;

    public function __construct()
    {
        if(auth('api')->check()){
            $this->user_id = auth('api')->user()->user_id;
        }
    }

    /*首页返回信息*/
    public function homepage(Request $request)
    {
        $re_data = $request->all();
        $data['banner'] = Banner::query()->get()->toArray();
        $data['info'] = [];
        //统计昨日/今日/本周/本月/上月/总 - 收益
        $data['info']['yesterday'] = UserDaily::query()->where('user_id',$this->user_id)
            ->where('create_time',Carbon::yesterday()->toDateString())->sum('task');
        $data['info']['today'] = UserDaily::query()->where('user_id',$this->user_id)
            ->where('create_time',now()->toDateString())->sum('task');
        $data['info']['week'] = UserDaily::query()->where('user_id',$this->user_id)
            ->whereDate('create_time','>=',Carbon::now()->startOfWeek()->toDateString())
            ->whereDate('create_time','<=',Carbon::now()->endOfWeek()->toDateString())
            ->sum('task');
        $data['info']['month'] = UserDaily::query()->where('user_id',$this->user_id)
            ->whereDate('create_time','>=',Carbon::now()->firstOfMonth()->toDateString())
            ->whereDate('create_time','<=',Carbon::now()->toDateString())
            ->sum('task');
        $data['info']['last_month'] = UserDaily::query()->where('user_id',$this->user_id)
            ->whereDate('create_time','>=',Carbon::now()->subMonth()->firstOfMonth()->toDateString())
            ->whereDate('create_time','<=',Carbon::now()->subMonth()->lastOfMonth()->toDateString())
            ->sum('task');
        // 今日已完成任务
        $data['info']['today_count'] = UserDaily::query()->where('user_id',$this->user_id)
            ->where('create_time',now()->toDateString())->value('count');
        // 今日剩余次数
        $count = UserRob::query()->where('user_id',$this->user_id)
            ->whereIn('status',[1,2,3])
            ->whereDate('create_time',now()->toDateString())
            ->count();

        $data['info']['count'] = UserDaily::query()->where('user_id',$this->user_id)->value('count');

        $vip = auth()->user()->vip;
        if($vip == 1){
            $today_count = 10;
        }else if($vip == 2){
            $today_count = 20;
        }else if($vip == 3){
            $today_count = 30;
        }else{
            $today_count = 3;
        }
        $data['info']['surplus_count'] = $today_count - $count;


        return api_response(true,$data);
    }

}
