<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\TimeOrderTableRequest;
use App\Http\Resources\TimeOrderTableResource;
use App\Models\TimeOrderTable;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;

class TimeOrderTableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TimeOrderTableRequest $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại'], 404);
        }
    
        $tableId = $request->get('table_id');
        $dateOrder = $request->get('date_oder');
        $timeOrder = $request->get('time_oder');
    
        $existingOrder = TimeOrderTable::where('table_id', $tableId)
            ->where('date_oder', $dateOrder)
            ->get();
    
        foreach ($existingOrder as $order) {
            $existingTime = \Carbon\Carbon::createFromFormat('H:i:s', $order->time_oder);
            $newTime = \Carbon\Carbon::createFromFormat('H:i:s', $timeOrder);
    
            if ($existingTime->diffInMinutes($newTime) < 60) {
                return response()->json(['message' => 'Thời gian đặt bàn phải cách nhau ít nhất 1 giờ'], 422);
            }
        }
    
        $res = TimeOrderTable::create([
            'table_id' => $tableId,
            'user_id' => $user->id,
            'phone_number' => $request->get('phone_number'),
            'date_oder' => $dateOrder,
            'time_oder' => $timeOrder,
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
            return response()->json(['message' => 'Không tìm thấy order cần sửa']);
        }
    
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại'], 404);
        }
    
        $tableId = $request->get('table_id');
        $dateOrder = $request->get('date_oder');
        $timeOrder = $request->get('time_oder');
    
        $existingOrder = TimeOrderTable::where('table_id', $tableId)
            ->where('date_oder', $dateOrder)
            ->where('id', '!=', $id) 
            ->get();
    
        foreach ($existingOrder as $order) {
            $existingTime = \Carbon\Carbon::createFromFormat('H:i:s', $order->time_oder);
            $newTime = \Carbon\Carbon::createFromFormat('H:i:s', $timeOrder);
    
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
                'time_oder' => $timeOrder,
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
