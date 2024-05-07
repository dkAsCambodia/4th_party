<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\WhitelistIP;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;

class WhitelistIPMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $whitelistIps = WhitelistIP::where('status', 1)->pluck('address')->toArray();
    //     $user = User::with(['roles'])->where('email', $request->name)->get()->first();
    //     print_r($whitelistIps);die;
    //     // supper admin
    //     if($user){
    //         if($user['roles'][0]['name'] == 'super admin'){
    //             return $next($request);
    //         }
    //     }
    //    // for cookie and login do not have params require->name
    //     if($user == null) {
    //         return $next($request);
    //     }
        // If no whitelist ip
        if (count($whitelistIps) != 0) {
            //return $next($request);
        }

        // If the ip is matched, return true

        if (in_array($request->ip(), $whitelistIps)) {
            return $next($request);
        }

        foreach ($whitelistIps as $ip) {
            $wildcardPos = strpos($ip, '*');

            // Check if the ip has a wildcard
            if ($wildcardPos !== false && substr($request->ip(), 0, $wildcardPos) == substr($ip, 0, $wildcardPos)) {
                return $next($request);
            }
        }
       // return view('auth.login');
        Toastr::error('fail, Your current IP Address is restricted to access the System. Please contact the administrator)','Error');
        return response()->view('auth.login');
        // return response()->json([
        //     'messages' => ['Your current IP Address is restricted to access the System. Please contact the administrator',    $request->name , $request->ip()],
        // ], 403);
    }
}
