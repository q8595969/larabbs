<?php

namespace App\Http\Middleware;

use Closure;

class UserApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /*
       * 验证是否登录  重定向
       */
        if(!auth('api')->check()){
            return redirect("api/redirect");
        }
        return $next($request);
    }
}
