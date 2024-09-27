<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

//JWTAuth 
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {

        $user = User::create([
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
        ]);

        $accessToken = JWTAuth::fromUser($user);
        $refreshToken = JWTAuth::claims(['refresh' => true])->fromUser($user);

        DB::table('refresh_tokens')->insert([
            'user_id' => $user->id,
            'token' => hash('sha256', $refreshToken),
            'expires_at' => now()->addDays(7),
        ]);


        return response()->json([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'user' => $user,
            'expires_in' => JWTAuth::factory()->getTTL() * 60 // 60 phút
        ]);
    }



    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$accessToken = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // $user = JWTAuth::user();
        $user = User::with('roles')->find(auth()->user()->id);
        $refreshToken = JWTAuth::claims(['refresh' => true])->fromUser(auth()->user());

        // Lưu refresh_token vào database
        DB::table('refresh_tokens')->insert([
            'user_id' => $user->id,
            'token' => hash('sha256', $refreshToken),
            'expires_at' => now()->addDays(7),
        ]);

        return response()->json([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'user' => $user,
            'expires_in' => JWTAuth::factory()->getTTL() * 60 // 60 phút
        ]);
    }



    public function getUser(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            return response()->json([
                'user' => new UserResource($user),
            ], 201);
        } catch (JWTException $e) {

            return response()->json(['error' => 'Token không hợp lệ or hết hạn'], 401);
        }
    }


    public function refreshToken(Request $request)
    {

        $refreshToken = $request->input('refresh_token');

        $storedToken = DB::table('refresh_tokens')
            ->where('token', hash('sha256', $refreshToken))
            ->where('revoked', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$storedToken) {
            return response()->json(['error' => 'token không hợp lệ hoặc đã hết hạn'], 401);
        }

        $user = User::find($storedToken->user_id);
        $newAccessToken = JWTAuth::fromUser($user);

        return response()->json([
            'access_token' => $newAccessToken,
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ]);
    }



    public function logout(Request $request)
    {
        $refreshToken = $request->input('refresh_token');

        DB::table('refresh_tokens')
            ->where('token', hash('sha256', $refreshToken))
            ->update(['revoked' => true]);

        $token = $request->bearerToken();

        if ($token) {
            JWTAuth::setToken($token)->invalidate();
        }

        return response()->json(['message' => 'logout thành công']);
    }
}
