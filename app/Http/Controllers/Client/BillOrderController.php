<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bill\Client\BillOderRequest;
use App\Http\Requests\Bill\Client\ItemBillRequest;
use App\Http\Resources\Client\BillOrderResource;
use App\Models\Bill;
use App\Models\BillDetail;
use App\Models\OrderCart;
use Illuminate\Http\Request;

class BillOrderController extends Controller
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

    public function addItem(ItemBillRequest $request)
    {
        $bill = Bill::where('ma_bill', $request->ma_bill)
            ->where('order_type', 'in_restaurant')
            ->where('status', 'pending')
            ->first();

        if (!$bill) {
            return response()->json([
                'success' => false,
                'message' => 'Bill không tồn tại hoặc đã được thanh toán.',
            ], 404);
        }


        $orderCartItems = OrderCart::whereIn('id', $request->id_order_cart)->get();
        if ($orderCartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'bạn đang cố tình thêm sản phẩm không có trong giỏ.',
            ], 404);
        }


        $billDetailsData = $orderCartItems->map(function ($item) use ($bill) {
            return [
                'bill_id' => $bill->id,
                'product_detail_id' => $item->product_detail_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'status' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();


        BillDetail::insert($billDetailsData);

        OrderCart::whereIn('id', $request->id_order_cart)->delete();


        return response()->json([
            'success' => true,
            'message' => 'Các sản phẩm đã được thêm vào hóa đơn thành công.',
        ]);
    }
}
