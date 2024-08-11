<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

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

    // protected function redirectTo($request)
    // {
    //     if (!$request->expectsJson()) {
    //         return route('login');
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
