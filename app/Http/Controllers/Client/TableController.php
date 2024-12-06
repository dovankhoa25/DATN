<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bill\BillOpenTableRequest;
use App\Http\Requests\Bill\BillRequest;
use App\Http\Requests\Bill\OpenTablesRequest;
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
use Illuminate\Support\Str;

class TableController extends Controller
{



    private function randomMaBill()
    {
        return 'BILL_' . Str::uuid()->toString();
    }


    public function getAllTables(Request $request)
    {
        $perPage = $request['per_page'] ?? 10;

        $tables = Table::where('status', true)->select('id', 'table', 'description', 'status')->paginate($perPage);

        return response()->json([
            'data' => $tables
        ]);
    }


    public function openTable(BillOpenTableRequest $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $openHour = 8;
        $closeHour = 22;

        $currentHour = now()->hour;

        if ($currentHour < $openHour || $currentHour >= $closeHour) {
            return response()->json(['message' => 'Nhà hàng đã đóng cửa. Vui lòng quay lại trong khung giờ từ 8h sáng đến 22h tối.'], 400);
        }

        $table = Table::find($request->table_id);
        if (!$table || !$table->status) {
            return response()->json([
                'success' => false,
                'message' => 'Bàn không tồn tại hoặc bị hỏng',
            ], 404);
        }

        if ($table->reservation_status != 'close') {
            return response()->json([
                'success' => false,
                'message' => 'Bàn đang được sử dụng hoặc không sẵn sàng',
            ], 400);
        }

        DB::beginTransaction();
        try {

            $bill = Bill::create([
                'ma_bill' => $this->randomMaBill(),
                'user_id' => $user->id,
                'customer_id' => null,
                'order_date' => Carbon::now(),
                'user_addresses_id' => null,
                'total_amount' => 0.00,
                'branch_address' => $request->branch_address ?? 'Fpoly',
                'payment_id' => $request->payment_id ?? null,
                // 'voucher_id' => null,
                'note' => null,
                'order_type' => 'in_restaurant',
                'table_number' => $table->id,
                'status' => 'pending',
                'qr_expiration' => null,
                'payment_status' => 'pending',
            ]);

            $table->update([
                'reservation_status' => 'open'
            ]);

            $tableIds = is_array($request->table_id) ? $request->table_id : [$request->table_id];

            $bill->tables()->attach($tableIds);
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


    public function openTables(OpenTablesRequest $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $openHour = 8;
        $closeHour = 23;

        $currentHour = now()->hour;

        if ($currentHour < $openHour || $currentHour >= $closeHour) {
            return response()->json(['message' => 'Nhà hàng đã đóng cửa. Vui lòng quay lại trong khung giờ từ 8h sáng đến 22h tối.'], 400);
        }
        $tables = Table::whereIn('id', $request->table_ids)->get();
        if ($tables->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không có bàn nào hợp lệ',
            ], 404);
        }

        $invalidTables = $tables->filter(fn($table) => !$table->status || $table->reservation_status != 'close');
        if ($invalidTables->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Một số bàn không sẵn sàng: ' . $invalidTables->pluck('id')->join(', '),
            ], 400);
        }


        DB::beginTransaction();
        try {

            $bill = Bill::create([
                'ma_bill' => $this->randomMaBill(),
                'user_id' => $user->id,
                'customer_id' => null,
                'order_date' => Carbon::now(),
                'user_addresses_id' => null,
                'total_amount' => 0.00,
                'branch_address' => $request->branch_address ?? 'Fpoly',
                'payment_id' => $request->payment_id ?? null,
                'note' => null,
                'order_type' => 'in_restaurant',
                'table_number' => null,
                'status' => 'pending',
                'qr_expiration' => null,
                'payment_status' => 'pending',
            ]);

            $bill->tables()->attach($request->table_ids);

            Table::whereIn('id', $request->table_ids)->update([
                'reservation_status' => 'open',
            ]);


            DB::commit();

            return response()->json([
                'message' => 'Bàn đã được mở thành công',
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
}
