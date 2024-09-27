<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ProfileRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UpdateProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại'], 404);
        }
    
        $profileItem = User::with(['customer', 'addresses'])->where('id', $user->id)->first();
    
        if ($profileItem) {
            $profileItem->makeHidden(['email_verified_at','created_at', 'updated_at']);
            $profileItem->customer->makeHidden(['created_at', 'updated_at']);
            $profileItem->addresses->makeHidden(['created_at', 'updated_at']);
    
            return response()->json([
                'data' => $profileItem,
                'message' => 'success'
            ], 200);
        } else {
            return response()->json(['message' => 'Không tìm thấy thông tin người dùng'], 404);
        }
    }
    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProfileRequest $request, int $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại'], 404);
        }

        if ($id !== $user->id) {
            return response()->json(['message' => 'Sai id'], 404);
        }

        if ($request->get('new_password') !== $request->get('confirm_password')) {
            return response()->json(['message' => 'Xác nhận mật khẩu mới sai'], 404);
        }

        $profileItem = DB::table('users')
            ->select('password', 'name')
            ->where('id', $user->id)
            ->first();

        $checkPass = $profileItem->password;
        $userName = $request->get('name') ?? $profileItem->name;

        if (!Hash::check($request->get('old_password'), $checkPass)) {
            return response()->json(['message' => 'Sai mật khẩu cũ'], 404);
        }

        if (Hash::check($request->get('new_password'), $checkPass)) {
            return response()->json(['message' => 'Mật khẩu mới không được trùng với mật khẩu cũ'], 404);
        }

        $newPassword = $request->get('new_password');
        $hashedPassword = Hash::make($newPassword);

        DB::table('users')
            ->where('id', $id)
            ->update([
                'name' => $userName,
                'password' => $hashedPassword,
            ]);

        $userUpdate = DB::table('users')->where('id', $id)->first();

        if ($userUpdate) {
            return response()->json([
                'message' => 'success'
            ], 200);
        } else {
            return response()->json(['message' => 'Cập nhật profile thất bại'], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
