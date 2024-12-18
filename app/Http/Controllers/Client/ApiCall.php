<?php

namespace App\Http\Controllers\Client;

use App\Events\Call;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiCall extends Controller
{
    public function goiNhanvien(int $id)
    {
        broadcast(new Call([
            'bill_id' => $id,
        ]));
        return response()->json([
            'message' => 'gọi nhân viên thành công.',
        ]);
    }
}
