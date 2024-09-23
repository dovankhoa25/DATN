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
    public function getAllTables()
    {
        $perPage = $request['per_page'] ?? 10;

        $tables = Table::paginate($perPage);

        return TableResource::collection($tables);
    }

    // nhận id table, user -> insert time order
    // return id time_order , stk, tiền,
    public function BookTable(TimeOrderTableRequest $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $tableId = $request->table_id;
        $dateOrder = $request->date_oder;
        $timeOrder = $request->time_oder;

        // Thời gian bắt đầu và kết thúc đặt bàn
        $startTime = Carbon::parse("$dateOrder $timeOrder");
        $endTime = $startTime->copy()->addMinutes(59);

        // Kiểm tra xem bàn đã được đặt trong khoảng thời gian này chưa
        $existingBooking = TimeOrderTable::where('table_id', $tableId)
            ->whereDate('date_oder', $dateOrder)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('time_oder', [$startTime, $endTime])
                    ->orWhereBetween(DB::raw('ADDTIME(time_oder, "00:59:00")'), [$startTime, $endTime]);
            })
            ->first();

        if ($existingBooking) {

            // Tìm bàn khác còn trống vào khoảng thời gian này
            $availableTables = DB::table('tables')
                ->whereNotIn('id', function ($query) use ($dateOrder, $startTime, $endTime) {
                    $query->select('table_id')
                        ->from('time_order_table')
                        ->whereDate('date_oder', $dateOrder)
                        ->where(function ($q) use ($startTime, $endTime) {
                            $q->whereBetween('time_oder', [$startTime, $endTime])
                                ->orWhereBetween(DB::raw("DATE_ADD(time_oder, INTERVAL 59 MINUTE)"), [$startTime, $endTime]);
                        });
                })
                ->get();

            return response()->json([
                'message' => "Bàn đã được đặt vào thời gian này",
                'table_available' => $availableTables,
            ], 409);
        }

        DB::beginTransaction();
        try {
            $timeOrder = TimeOrderTable::create([
                'table_id' => $request->table_id,
                'user_id' => $user->id,
                'phone_number' => $request->phone_number,
                'date_oder' => $request->date_oder,
                'time_oder' => $request->time_oder,
                'description' => $request->description ?? Null,
                'status' => 'pending',
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Đặt bàn thành công.',
                'booking_id' => $timeOrder->id,
                'stk' => '0123456789',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại sau'], 500);
        }
    }
}
