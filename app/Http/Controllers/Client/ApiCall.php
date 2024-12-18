<?php

namespace App\Http\Controllers\Client;

use App\Events\Call;
use App\Http\Controllers\Controller;
use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiCall extends Controller
{
    public function goiNhanvien(int $id)
    {

        $table = DB::table('bill_table')
            ->join('tables', 'bill_table.table_id', '=', 'tables.id')
            ->where('bill_table.bill_id', $id)
            ->select('bill_table.*', 'tables.table', 'tables.description', 'tables.min_guest', 'tables.max_guest', 'tables.status')  // Chọn các trường cần thiết từ cả hai bảng
            ->get();


        if (!$table) {
            return response()->json([
                'message' => 'Không tìm thấy bàn liên quan đến hóa đơn này.',
            ], 404);
        }

        broadcast(new Call([
            'bill_id' => $id,
            'table_id' => $table,
        ]));

        return response()->json([
            'message' => 'Gọi nhân viên thành công.',
            'table_id' => $table
        ]);
    }
}
