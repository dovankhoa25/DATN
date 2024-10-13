<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\AddressRequest;
use App\Http\Requests\Profile\ProfileRequest;
use App\Models\User;
use App\Models\UserAddress;
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
            if (!$profileItem->customer) {
                $profileItem->setRelation('customer', collect());
            }

            if ($profileItem->addresses->isEmpty()) {
                $profileItem->setRelation('addresses', collect());
            }
    
            $profileItem->makeHidden(['email_verified_at', 'created_at', 'updated_at']);
            $customer = $profileItem->customer instanceof \Illuminate\Database\Eloquent\Model 
                ? $profileItem->customer->makeHidden(['created_at', 'updated_at'])->toArray()
                : [];
    
            $addresses = $profileItem->addresses->map(function ($address) {
                return collect($address)->except(['created_at', 'updated_at'])->toArray();
            });
    
            $profileItemArray = $profileItem->toArray();
            $profileItemArray['customer'] = $customer;
            $profileItemArray['addresses'] = $addresses;
    
            return response()->json([
                'data' => $profileItemArray,
                'message' => 'success'
            ], 200);
        } else {
            return response()->json(['message' => 'Không tìm thấy thông tin người dùng'], 404);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeAddress(AddressRequest $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại'], 404);
        }

        if ($request->get('is_default') == 1) {
            UserAddress::where('user_id', $user->id)
                ->where('is_default', 1)
                ->update(['is_default' => 0]);
        }

        $res = UserAddress::create([
            'user_id' => $user->id,
            'address' => $request->get('address'),
            'city' => $request->get('city'),
            'state' => $request->get('state'),
            'postal_code' => $request->get('postal_code'),
            'country' => $request->get('country'),
            'is_default' => $request->get('is_default')
        ]);

        if ($res) {
            return response()->json([
                'data' => $res->makeHidden(['created_at', 'updated_at']),
                'message' => 'success'
            ], 201);
        } else {
            return response()->json(['error' => 'Thêm thất bại'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function updateAddress(AddressRequest $request, int $idAddress)
    {
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại'], 404);
        }

        $checkAddress = DB::table('user_addresses')
            ->where('id', $idAddress)
            ->where('user_id', $user->id)
            ->first();

        if(!$checkAddress){
            return response()->json(['error' => 'Bạn không thể sửa địa chỉ này'], 400);
        }

        if ($request->get('is_default') == 1) {
            UserAddress::where('user_id', $user->id)
                ->where('is_default', 1)
                ->update(['is_default' => 0]);
        }

        DB::table('user_addresses')
        ->where('id', $idAddress)
        ->update([
            'user_id' => $user->id,
            'address' => $request->get('address'),
            'city' => $request->get('city'),
            'state' => $request->get('state'),
            'postal_code' => $request->get('postal_code'),
            'country' => $request->get('country'),
            'is_default' => $request->get('is_default')
        ]);
        $addressAupdate = DB::table('user_addresses')->where('id', $idAddress)->first();
        if ($addressAupdate) {
            return response()->json([
                'data' => $addressAupdate,
                'message' => 'success'
            ], 200);
        } else {
            return response()->json(['error' => 'Sửa thất bại'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProfileRequest $request, int $idUser)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại'], 404);
        }

        if ($idUser !== $user->id) {
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
            ->where('id', $idUser)
            ->update([
                'name' => $userName,
                'password' => $hashedPassword,
            ]);

        $userUpdate = DB::table('users')->where('id', $idUser)->first();

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
    public function destroyAddress(int $idAddress)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $address = UserAddress::findOrFail($idAddress);

        if($user->id != $address->user_id){
            return response()->json(['error' => 'Bạn không thể xóa địa chỉ này'],400);
        }

        if($address->is_default == 1){
            return response()->json(['error' => 'Không thể xóa địa chỉ mặc định'],400);
        }

        $res = $address->delete();

        if ($res) {
            return response()->json(['message' => 'success'], 204);
        } else {
            return response()->json(['error' => 'Xóa thất bại']);
        }
    }
}
