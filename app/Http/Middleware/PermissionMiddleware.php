<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class PermissionMiddleware
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
        // Permission is not required for route with no name
        $permission = Route::currentRouteName();
        // dd(Auth::user()->can($permission));
        if(Auth::user()->can($permission)) {
            return $next($request);
        }



        // User does not have permission
        if ($request->expectsJson()) {
            return response()->json([
                'messages' => ['User does not have the right permissions. Necessary permissions is '.$permission],
                'permission' => $permission,
            ], 403);
        }

        return abort(403);
    }
}
