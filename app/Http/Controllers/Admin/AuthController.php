<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

//JWTAuth 
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
       
        $user = User::create([
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'data' => new UserResource($user),
            'token' => $token,
        ], 201);
        
    }



    // public function login(LoginRequest $request)
    // {
    //     $credentials = $request->only('email', 'password');

    //     try {
    //         if (! $token = JWTAuth::attempt($credentials)) {
    //             return response()->json(['error' => 'invalid_credentials'], 400);
    //         }
    //     } catch (JWTException $e) {
    //         return response()->json(['error' => 'could_not_create_token'], 500);
    //     }
    //     $user = JWTAuth::user();
    //     return response()->json(compact('token','user'));
    // }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
    
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'kut'], 400);
            }
            $user = JWTAuth::user();
            if ($user->is_locked) {
                return response()->json(['error' => 'ặc bị khía rồi đừng vào'], 403);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        return response()->json(compact('token', 'user'));
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
}

