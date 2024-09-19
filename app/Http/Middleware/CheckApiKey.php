<?php

namespace App\Http\Middleware;

use App\Models\Client;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('api_key');

        if (!$apiKey || !Client::where('api_key', hash('sha256', $apiKey))->exists()) {
            return response()->json([
                'message' => 'xin api key nhắn tin admin ok'
            ], 401);
        }
    
        return $next($request);
    }
}
