<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\TimeOrderTable\TimeOrderTableRequest;
use App\Http\Resources\TimeOrderTableResource;
use App\Models\TimeOrderTable;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TimeOrderTableController extends Controller
{

    public function index(Request $request)
    {
        $validated = $request->validate([
            'per_page' => 'integer|min:1|max:100'
        ]);
        $perPage = $validated['per_page'] ?? 10;

        $currentDate = Carbon::now('Asia/Ho_Chi_Minh')->toDateString();
        $currentTime = Carbon::now('Asia/Ho_Chi_Minh')->toTimeString();
        $thresholdTime = Carbon::now('Asia/Ho_Chi_Minh')->addMinutes(30)->toTimeString();

        $orderedTables = DB::table('time_order_table')
            ->where('date_oder', $currentDate)
            ->whereTime('time_oder', '>=', $currentTime)
            ->whereTime('time_oder', '<=', $thresholdTime)
            ->pluck('table_id');

        DB::table('tables')
            ->whereIn('id', $orderedTables)
            ->update(['status' => 2]);

        $tablesWithStatus2 = DB::table('tables')
            ->where('status', 2)
            ->pluck('id');

        $tableItem = DB::table('tables')->paginate($perPage);

        if ($tableItem->total() > 0) {
            return response()->json([
                'data' => $tableItem,
                'message' => 'success'
            ], 200);
        } else {
            return response()->json(['message' => 'Dữ liệu trống'], 404);
        }
    }


    public function store(TimeOrderTableRequest $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại'], 404);
        }

        $tableId = $request->get('table_id');
        $dateOrder = $request->get('date_oder');
        $timeOrderKey = $request->get('time_oder');

        $hourOrder = [
            'sáng' => '07:00:00',
            'trưa' => '12:00:00',
            'tối'  => '19:00:00',
        ];

        if (!isset($hourOrder[$timeOrderKey])) {
            return response()->json(['message' => 'Khung giờ không hợp lệ'], 422);
        }

        $newTime = \Carbon\Carbon::createFromFormat('H:i:s', $hourOrder[$timeOrderKey]);

        if ($dateOrder == \Carbon\Carbon::today('Asia/Ho_Chi_Minh')->toDateString()) {
            $currentTime = \Carbon\Carbon::now('Asia/Ho_Chi_Minh')->toTimeString();
            if ($hourOrder[$timeOrderKey] <= $currentTime) {
                return response()->json(['message' => 'Khung giờ đã qua, vui lòng chọn khung giờ khác'], 422);
            }
        }

        $existingOrder = TimeOrderTable::where('table_id', $tableId)
            ->where('date_oder', $dateOrder)
            ->get();

        foreach ($existingOrder as $order) {
            $existingTime = \Carbon\Carbon::createFromFormat('H:i:s', $order->time_oder);

            if ($existingTime->diffInMinutes($newTime) < 60) {
                return response()->json(['message' => 'Bàn đã được đặt trong khung giờ này'], 422);
            }
        }

        $res = TimeOrderTable::create([
            'table_id' => $tableId,
            'user_id' => $user->id,
            'phone_number' => $request->get('phone_number'),
            'date_oder' => $dateOrder,
            'time_oder' => $hourOrder[$timeOrderKey],
            'description' => $request->get('description'),
            'status' => 'pending',
        ]);

        $orderTableCollection = new TimeOrderTableResource($res);
        if ($res) {
            return response()->json([
                'data' => $orderTableCollection,
                'message' => 'success'
            ], 201);
        } else {
            return response()->json(['error' => 'Thêm thất bại'], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $idTable)
    {
        $objOrderTable = new TimeOrderTable();
        $validated = $request->validate([
            'per_page' => 'integer|min:1|max:100'
        ]);
        $perPage = $validated['per_page'] ?? 10;

        $data = $objOrderTable->timeOrderByTableId($idTable)->paginate($perPage);

        if ($data->total() > 0) {
            return response()->json([
                'data' => $data,
                'message' => 'success'
            ], 200);
        } else {
            return response()->json(['message' => 'Bàn chưa có lịch đặt trước'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TimeOrderTableRequest $request, int $id)
    {
        $orderItem = DB::table('time_order_table')->where('id', $id)->first();
        if (!$orderItem) {
            return response()->json(['message' => 'Không tìm thấy order cần sửa'], 404);
        }

        $user = JWTAuth::parseToken()->authenticate();
        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại'], 404);
        }

        $tableId = $request->get('table_id');
        $dateOrder = $request->get('date_oder');
        $timeOrderKey = $request->get('time_oder');

        $hourOrder = [
            'sáng' => '07:00:00',
            'trưa' => '12:00:00',
            'tối'  => '19:00:00',
        ];

        if (!isset($hourOrder[$timeOrderKey])) {
            return response()->json(['message' => 'Khung giờ không hợp lệ'], 422);
        }

        $newTime = \Carbon\Carbon::createFromFormat('H:i:s', $hourOrder[$timeOrderKey]);

        $existingOrder = TimeOrderTable::where('table_id', $tableId)
            ->where('date_oder', $dateOrder)
            ->where('id', '!=', $id)
            ->get();

        foreach ($existingOrder as $order) {
            $existingTime = \Carbon\Carbon::createFromFormat('H:i:s', $order->time_oder);

            if ($existingTime->diffInMinutes($newTime) < 60) {
                return response()->json(['message' => 'Thời gian đặt bàn phải cách nhau ít nhất 1 giờ'], 422);
            }
        }

        // Cập nhật bản ghi
        DB::table('time_order_table')
            ->where('id', $id)
            ->update([
                'table_id' => $tableId,
                'user_id' => $user->id,
                'phone_number' => $request->get('phone_number'),
                'date_oder' => $dateOrder,
                'time_oder' => $hourOrder[$timeOrderKey],
                'description' => $request->get('description'),
                'status' => 'pending',
            ]);

        $updateOrderItem = DB::table('time_order_table')->where('id', $id)->first();
        $orderTableCollection = new TimeOrderTableResource($updateOrderItem);

        return response()->json([
            'data' => $orderTableCollection,
            'message' => 'success'
        ], 200);
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $orderTable = TimeOrderTable::findOrFail($id);

        $res = $orderTable->delete();
        if ($res) {
            return response()->json(['message' => 'success'], 204);
        } else {
            return response()->json(['error' => 'Xóa thất bại']);
        }
    }
}
