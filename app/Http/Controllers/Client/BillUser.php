<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bill\Client\StoreBillRequest;
use App\Http\Resources\BillResource;
use App\Models\Bill;
use App\Models\BillDetail;
use App\Models\Customer;
use App\Models\OnlineCart;
use App\Models\ProductDetail;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

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



    private function randomMaBill()
    {
        return 'BILL_' . (string) Str::uuid();
    }



    public function store(StoreBillRequest $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $selectedItems = $request->get('productdetail_items');
        $usePoints = $request->get('use_points', false);
        $voucherId = $request->get('voucher_id');
        if (empty($selectedItems)) {
            return response()->json(['message' => 'Không có sản phẩm nào được chọn'], 400);
        }

        $totalAmount = 0;


        foreach ($selectedItems as $item) {
            $productDetail = ProductDetail::find($item['product_detail_id']);

            if (!$productDetail || $productDetail->quantity < $item['quantity']) {
                return response()->json([
                    'error' => 'Số lượng đặt vượt quá số lượng hiện có của sản phẩm hoặc sản phẩm không tồn tại.',
                    'product_detail_id' => $item['product_detail_id']
                ], 400);
            }

            $price = $productDetail->sale ?? $productDetail->price;
            $totalAmount += $price * $item['quantity'];
        }


        if ($voucherId) {
            $voucher = Voucher::find($voucherId);
            if ($voucher) {
                $totalAmount -= $voucher->discount_amount;
                if ($totalAmount < 0) $totalAmount = 0;
            }
        }

        $customer = Customer::where('user_id', $user->id)->first();
        if ($usePoints && $customer) {
            $diemthuong = $customer->diemthuong;

            if ($diemthuong > 0) {
                if ($diemthuong >= $totalAmount) {
                    $diemtru = $totalAmount;
                    $customer->diemthuong -= $diemtru;
                    $totalAmount = 0;
                } else {
                    $diemtru = $diemthuong;
                    $totalAmount -= $diemthuong;
                    $customer->diemthuong = 0;
                }

                $customer->save();
            }
        }



        $bill = Bill::create([
            'ma_bill' => $this->randomMaBill(),
            'user_id' => $user->id,
            'customer_id' => null,
            'user_addresses_id' => $request->get('user_addresses_id'),
            'order_date' => now(),
            'total_amount' => $totalAmount,
            'branch_address' => $request->get('branch_address'),
            'payment_id' => $request->get('payment_id'),
            'voucher_id' => $voucherId,
            'note' => $request->get('note'),
            'order_type' => $request->get('order_type', 'online'),
            'status' => 'pending',
            'table_number' => $request->get('table_number'),
        ]);


        foreach ($selectedItems as $item) {
            BillDetail::create([
                'bill_id' => $bill->id,
                'product_detail_id' => $item['product_detail_id'],
                'quantity' => $item['quantity'],
            ]);


            $productDetail = ProductDetail::find($item['product_detail_id']);
            $productDetail->quantity -= $item['quantity'];
            $productDetail->save();
        }


        OnlineCart::where('user_id', $user->id)
            ->whereIn('product_detail_id', array_column($selectedItems, 'product_detail_id'))
            ->delete();

        return response()->json([
            'message' => 'Đặt hàng thành công',
            'bill' => new BillResource($bill)
        ], 201);
    }



    private function handleRefund($bill)
    {
        // Xử lý hoàn tiền cho người dùng
        // Nếu người dùng đã thanh toán bằng tiền mặt, chuyển khoản hoặc điểm thưởng
        // Bạn cần triển khai logic hoàn tiền tương ứng
    }

    
    private function notifyUser($bill)
    {
        $user = User::find($bill->user_id);
    }


    public function requestCancelBill($id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $bill = Bill::where('id', $id)->where('user_id', $user->id)->first();

        if (!$bill) {
            return response()->json(['message' => 'Không tìm thấy hóa đơn'], 404);
        }

        if (in_array($bill->status, ['completed', 'cancelled', 'failed', 'shipping'])) {
            return response()->json(['message' => 'Đơn hàng không thể hủy ở trạng thái hiện tại'], 400);
        }

        if ($bill->status == 'cancellation_requested') {
            return response()->json(['message' => 'Bạn đã gửi yêu cầu hủy đơn hàng này rồi'], 400);
        }

        $bill->status = 'cancellation_requested';
        $bill->save();

        return response()->json(['message' => 'Yêu cầu hủy đơn hàng đã được gửi'], 200);
    }
}
