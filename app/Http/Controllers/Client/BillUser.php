<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class BillUser extends Controller
{
    public function billUser()
{
    $user = JWTAuth::parseToken()->authenticate();

    $bills = Bill::where('user_id', $user->id)->get();

    $formattedBills = $bills->map(function ($bill) {
        if ($bill->order_type === 'online') {
            return [
                'ma_bill' => $bill->ma_bill,
                'id' => $bill->id,
                'user_id' => $bill->user_id,
                'user_addresses_id' => $bill->user_addresses_id,
                'order_date' => $bill->order_date,
                'total_amount' => $bill->total_amount,
                'payment_id' => $bill->payment_id,
                'voucher_id' => $bill->voucher_id,
                'note' => $bill->note,
                'order_type' => $bill->order_type,
                'status' => $bill->status,
            ];
        } elseif ($bill->order_type === 'in_restaurant') {
            return [
                'id' => $bill->id,
                'ma_bill' => $bill->ma_bill,
                'customer_id' => $bill->customer_id,
                'order_date' => $bill->order_date,
                'total_amount' => $bill->total_amount,
                'branch_address' => $bill->branch_address,
                'payment_id' => $bill->payment_id,
                'voucher_id' => $bill->voucher_id,
                'note' => $bill->note,
                'order_type' => $bill->order_type,
                'table_number' => $bill->table_number,
                'status' => $bill->status,
            ];
        }
    });

    return response()->json([
        'data' => $formattedBills,
        'total_bills' => $formattedBills->count(),
    ], 200);
}

}
