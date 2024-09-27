<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\TimeOrderTable\TimeOrderTableRequest;
use App\Http\Resources\TableResource;
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

    // nhận id table, user -> insert time order
    // return id time_order , stk, tiền,
    // public function openTable(TimeOrderTableRequest $request)
    // {
    //     $user = JWTAuth::parseToken()->authenticate();

    //     $tableId = $request->table_id;
    //     $dateOrder = $request->date_oder;
    //     $timeOrder = $request->time_oder;

    //     // Thời gian bắt đầu và kết thúc đặt bàn
    //     $startTime = Carbon::parse("$dateOrder $timeOrder");
    //     $endTime = $startTime->copy()->addMinutes(59);

    //     // Kiểm tra xem bàn đã được đặt trong khoảng thời gian này chưa
    //     $existingBooking = TimeOrderTable::where('table_id', $tableId)
    //         ->whereDate('date_oder', $dateOrder)
    //         ->where(function ($query) use ($startTime, $endTime) {
    //             $query->whereBetween('time_oder', [$startTime, $endTime])
    //                 ->orWhereBetween(DB::raw('ADDTIME(time_oder, "00:59:00")'), [$startTime, $endTime]);
    //         })
    //         ->first();

    //     if ($existingBooking) {

    //         // Tìm bàn khác còn trống vào khoảng thời gian này
    //         $availableTables = DB::table('tables')
    //             ->whereNotIn('id', function ($query) use ($dateOrder, $startTime, $endTime) {
    //                 $query->select('table_id')
    //                     ->from('time_order_table')
    //                     ->whereDate('date_oder', $dateOrder)
    //                     ->where(function ($q) use ($startTime, $endTime) {
    //                         $q->whereBetween('time_oder', [$startTime, $endTime])
    //                             ->orWhereBetween(DB::raw("DATE_ADD(time_oder, INTERVAL 59 MINUTE)"), [$startTime, $endTime]);
    //                     });
    //             })
    //             ->get();

    //         return response()->json([
    //             'message' => "Đặt bàn thất bại. Bàn này đã được đặt vào thời gian này",
    //             'table_available' => $availableTables,
    //         ], 409);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         $timeOrder = TimeOrderTable::create([
    //             'table_id' => $request->table_id,
    //             'user_id' => $user->id,
    //             'phone_number' => $request->phone_number,
    //             'date_oder' => $request->date_oder,
    //             'time_oder' => $request->time_oder,
    //             'description' => $request->description ?? Null,
    //             'status' => 'pending',
    //         ]);

    //         DB::commit();
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Đặt bàn thành công.',
    //             'booking_id' => $timeOrder->id,
    //             'stk' => '0123456789',
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại sau'], 500);
    //     }
    // }

    public function openTable(TimeOrderTableRequest $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $tableId = $request->table_id;
        $dateOrder = $request->date_oder;
        $timeOrderInput = $request->time_oder;

        $timeSlots = [
            'sáng' => '07:00:00',
            'trưa' => '12:00:00',
            'tối'  => '19:00:00',
        ];
        $selectedTime = $timeSlots[$timeOrderInput];

        // Kiểm tra xem bàn đã được đặt vào thời điểm này chưa
        $existingOrder = TimeOrderTable::where('table_id', $tableId)
            ->where('date_oder', $dateOrder)
            ->where('time_oder', $selectedTime)
            ->first();

        if ($existingOrder) {
            return response()->json([
                'message' => "Đặt bàn thất bại. Bàn này đã được đặt vào buổi $timeOrderInput",
            ], 409);
        }

        DB::beginTransaction();
        try {
            $timeOrder = TimeOrderTable::create([
                'table_id' => $tableId,
                'user_id' => $user->id,
                'phone_number' => $request->phone_number,
                'date_oder' => $dateOrder,
                'time_oder' => $selectedTime,
                'description' => $request->description ?? null,
                'status' => 'pending',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đặt bàn thành công',
                'time_oder_id' => $timeOrder->id,
                'stk' => '0123456789',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại sau'], 500);
        }
    }
}
