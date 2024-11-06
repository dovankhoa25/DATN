<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bill\Client\BillOderRequest;
use App\Http\Resources\Client\BillOrderResource;
use App\Models\Bill;
use Illuminate\Http\Request;

class BillOnlineController extends Controller
{
    public function getBillOnline(BillOderRequest $request)
    {

        $bill = Bill::where('ma_bill', $request->ma_bill)
            ->where('order_type', 'in_restaurant')
            ->where('status', 'pending')
            ->first();

        if ($bill) {
            return new BillOrderResource($bill);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Bill not fould ok.',
            ], 404);
        }
    }
}
