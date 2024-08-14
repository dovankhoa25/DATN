<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = JWTAuth::parseToken()->authenticate();

        
        // if (!$user || !in_array($user->role->name, $roles)) {
        //     return response()->json(['error' => 'Unauthorized'], 403);
        // }

        if (!$user || !$user->roles()->whereIn('name', $roles)->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        return $next($request);
    }
}

