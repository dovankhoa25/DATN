<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Tymon\JWTAuth\Facades\JWTAuth;

class Authenticate extends Middleware
{

    public function handle($request, Closure $next, ...$guards)
    {
        $this->authenticate($request, $guards);

        return $next($request);
    }


    protected function authenticate($request, array $guards)
    {
        if ($this->auth->guard($guards)->check()) {
            return $this->auth->shouldUse($guards);
        }

        $this->unauthenticated($request, $guards);
    }

    // protected function authenticate($request, array $guards)
    // {
    //     try {
    //         $user = JWTAuth::parseToken()->authenticate();
    //         if (!$user) {
    //             return $this->unauthenticated($request, $guards);
    //         }
    //     } catch (\Exception $e) {
    //         return $this->unauthenticated($request, $guards);
    //     }
    // }



    protected function unauthenticated($request, array $guards)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'chưa xác thực.'], 401);
        }

        return response()->json(['error' => 'chưa xác thực.'], 401);
    }
}
