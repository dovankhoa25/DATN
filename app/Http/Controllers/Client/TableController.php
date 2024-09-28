<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bill\BillOpenTableRequest;
use App\Http\Requests\Bill\BillRequest;
use App\Http\Requests\TimeOrderTable\TimeOrderTableRequest;
use App\Http\Resources\TableResource;
use App\Models\Bill;
use App\Models\Table;
use App\Models\TimeOrderTable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class TableController extends Controller
{
    public function getAllTables(Request $request)
    {
        $perPage = $request['per_page'] ?? 10;

        $tables = Table::select('id', 'table', 'description', 'status')->paginate($perPage);

        return response()->json([
            'data' => $tables
        ]);
    }

    // nhận id table, user -> tạo bill
    // return mã bill, table id
    public function openTable(BillOpenTableRequest $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $userRoles = $user->roles()->pluck('name')->toArray();

        if (!in_array('qtv', $userRoles) && !in_array('admin', $userRoles)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền mở bàn',
            ], 403);
        }

        $table = Table::find($request->table_id);
        if (!$table) {
            return response()->json([
                'success' => false,
                'message' => 'Bàn không tồn tại',
            ], 404);
        }

        if (!$table->status) {
            return response()->json([
                'success' => false,
                'message' => 'Bàn đang bị khóa',
            ], 404);
        }

        DB::beginTransaction();
        try {
            $maBill = 'BILL-' . strtoupper(uniqid());
            while (Bill::where('ma_bill', $maBill)->exists()) {
                $maBill = 'BILL-' . strtoupper(uniqid());
            }

            $bill = Bill::create([
                'ma_bill' => $maBill,
                'user_id' => $user->id,
                'customer_id' => $request->customer_id ?? null,
                'order_date' => Carbon::now(),
                'total_amount' => $request->total_amount ?? 0.00,
                'branch_address' => $request->branch_address ?? 'Fpoly',
                'payment_id' => $request->payment_id,
                'voucher_id' => $request->voucher_id ?? null,
                'note' => $request->note ?? null,
                'order_type' => 'in_restaurant',
                'user_addresses_id' => $request->user_addresses_id ?? null,
                'status' => 'pending',
                'table_number' => $table->id,
            ]);

            $table->update(['status' => false]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bàn đã được mở thành công',
                'table_id' => $table->id,
                'ma_bill' => $bill->ma_bill,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra, vui lòng thử lại sau',
            ], 500);
        }
    }




    // public function openTable(TimeOrderTableRequest $request)
    // {
    //     $user = JWTAuth::parseToken()->authenticate();

    //     $tableId = $request->table_id;
    //     $dateOrder = $request->date_oder;
    //     $timeOrderInput = $request->time_oder;

    //     $timeSlots = [
    //         'sáng' => '07:00:00',
    //         'trưa' => '12:00:00',
    //         'tối'  => '19:00:00',
    //     ];
    //     $selectedTime = $timeSlots[$timeOrderInput];

    //     // Kiểm tra xem bàn đã được đặt vào thời điểm này chưa
    //     $existingOrder = TimeOrderTable::where('table_id', $tableId)
    //         ->where('date_oder', $dateOrder)
    //         ->where('time_oder', $selectedTime)
    //         ->first();

    //     if ($existingOrder) {
    //         return response()->json([
    //             'message' => "Đặt bàn thất bại. Bàn này đã được đặt vào buổi $timeOrderInput",
    //         ], 409);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         $timeOrder = TimeOrderTable::create([
    //             'table_id' => $tableId,
    //             'user_id' => $user->id,
    //             'phone_number' => $request->phone_number,
    //             'date_oder' => $dateOrder,
    //             'time_oder' => $selectedTime,
    //             'description' => $request->description ?? null,
    //             'status' => 'pending',
    //         ]);

    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Đặt bàn thành công',
    //             'time_oder_id' => $timeOrder->id,
    //             'stk' => '0123456789',
    //         ], 200);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại sau'], 500);
    //     }
    // }
}
