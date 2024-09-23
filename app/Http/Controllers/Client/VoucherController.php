<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class VoucherController extends Controller
{
    public function changeVoucher(Request $request)
    {

        $request->validate([
            'points' => 'required|integer|min:1000',
        ]);
        $user = JWTAuth::parseToken()->authenticate();

        $customer = DB::table('customers')
            ->where('user_id', $user->id)
            ->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Khách hàng không tồn tại',
            ], 404);
        }

        if ($customer->diemthuong < $request->points) {
            return response()->json([
                'message' => 'Bạn không có đủ điểm'
            ], 400);
        }

        // 1000 điểm = 1.000 VND
        $voucherValue = $request->points / 1000;

        // Bắt đầu transaction để đảm bảo dữ liệu không bị hỏng nếu xảy ra lỗi
        DB::beginTransaction();

        try {
            $voucherId = DB::table('vouchers')->insertGetId([
                'name' => 'Voucher từ điểm thưởng',
                'value' => $voucherValue,
                'expiration_date' => Carbon::now()->addMonth(),
                'customer_id' => $customer->id,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('customers')
                ->where('id', $customer->id)
                ->update(['diemthuong' => $customer->diemthuong - $request->points]);

            $voucher = DB::table('vouchers')->where('id', $voucherId)->first();

            // Commit transaction nếu mọi thứ thành công
            DB::commit();

            return response()->json([
                'message' => 'Đổi voucher thành công',
                'voucher' => $voucher,
            ], 200);
        } catch (ModelNotFoundException $e) {
            // Rollback nếu có lỗi xảy ra
            DB::rollBack();

            return response()->json([
                'message' => 'Đổi voucher thất bại',
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Có lỗi xảy ra, vui lòng thử lại sau',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function vouchersCustomer()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $customer = DB::table('customers')
                ->where('user_id', $user->id)
                ->first();

            $vouchers = DB::table('vouchers')
                ->where('customer_id', $customer->id)
                ->where('status', true)
                ->select('id', 'name', 'value', 'expiration_date', 'status', 'created_at', 'updated_at')
                ->get();

            if ($vouchers->isEmpty()) {
                return response()->json([
                    'message' => 'Customer này không có voucher nào.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $vouchers,
            ], 200);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['error' => 'Token hết hạn'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'Token không hợp lệ'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Token không được cung cấp'], 401);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Không lấy được voucher',
            ], 500);
        }
    }
}
