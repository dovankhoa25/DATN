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

            $profileItem->makeHidden(['deleted_at', 'is_locked', 'email_verified_at', 'created_at', 'updated_at']);
            $customer = $profileItem->customer instanceof \Illuminate\Database\Eloquent\Model
                ? $profileItem->customer->makeHidden(['deleted_at', 'created_at', 'updated_at'])->toArray()
                : [];

            $addresses = $profileItem->addresses->map(function ($address) {
                return collect($address)->except(['deleted_at', 'created_at', 'updated_at'])->toArray();
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


    public function storeAddress(AddressRequest $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại'], 404);
        }

        $addressCount = UserAddress::where('user_id', $user->id)->count();
        if ($addressCount >= 5) {
            return response()->json(['message' => 'Người dùng chỉ có thể tạo tối đa 5 địa chỉ'], 403);
        }

        $hasDefaultAddress = UserAddress::where('user_id', $user->id)->where('is_default', 1)->exists();

        if (!$hasDefaultAddress) {
            $request['is_default'] = 1;
        } elseif ($request->get('is_default') == 1) {
            UserAddress::where('user_id', $user->id)
                ->where('is_default', 1)
                ->update(['is_default' => 0]);
        }

        $res = UserAddress::create([
            'user_id' => $user->id,
            'fullname' => $request->get('fullname'),
            'phone' => $request->get('phone'),
            'province' => $request->get('province'),
            'district' => $request->get('district'),
            'commune' => $request->get('commune'),
            'address' => $request->get('address'),
            'postal_code' => $request->get('postal_code') ?? 70000,
            'country' => $request->get('country') ?? 'Việt Nam Fpl',
            'is_default' => $request->get('is_default') ?? 1,
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

        if (!$checkAddress) {
            return response()->json(['error' => 'Bạn không thể sửa địa chỉ này'], 500);
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
                'fullname' => $request->get('fullname'),
                'phone' => $request->get('phone'),
                'province' => $request->get('province'),
                'district' => $request->get('district'),
                'commune' => $request->get('commune'),
                'address' => $request->get('address'),
                'postal_code' => $request->get('postal_code') ?? 70000,
                'country' => $request->get('country') ?? 'Việt Nam Fpl',
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


    public function destroyAddress(int $idAddress)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $address = UserAddress::findOrFail($idAddress);

        if ($user->id != $address->user_id) {
            return response()->json(['error' => 'Bạn không thể xóa địa chỉ này'], 400);
        }

        if ($address->is_default == 1) {
            return response()->json(['error' => 'Không thể xóa địa chỉ mặc định'], 400);
        }

        $res = $address->delete();

        if ($res) {
            return response()->json(['message' => 'success'], 204);
        } else {
            return response()->json(['error' => 'Xóa thất bại']);
        }
    }

    public function updateDefaultAddress(int $idAddress)
    {
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại'], 404);
        }

        $address = UserAddress::where('id', $idAddress)
            ->where('user_id', $user->id)
            ->first();

        if (!$address) {
            return response()->json(['error' => 'Địa chỉ không tồn tại hoặc không thuộc về bạn'], 403);
        }
        UserAddress::where('user_id', $user->id)
            ->where('is_default', 1)
            ->update(['is_default' => 0]);

        $address->update(['is_default' => 1]);

        return response()->json([
            'data' => $address->makeHidden(['created_at', 'updated_at']),
            'message' => 'Cập nhật địa chỉ mặc định thành công',
        ], 200);
    }
}
