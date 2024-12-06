<?php

namespace App\Http\Controllers\Client;

use App\Events\BillCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Bill\Client\StoreBillRequest;
use App\Http\Resources\BillDetailResource;
use App\Http\Resources\BillResource;
use App\Jobs\CheckBillExpiration;
use App\Models\Bill;
use App\Models\BillDetail;
use App\Models\BillVoucher;
use App\Models\Customer;
use App\Models\OnlineCart;
use App\Models\Payment;
use App\Models\ProductDetail;
use App\Models\ShippingHistory;
use App\Models\User;
use App\Models\Voucher;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class BillUser extends Controller
{
    public function billUser()
    {
        $user = JWTAuth::parseToken()->authenticate();

        $bills = Bill::where('user_id', $user->id)->latest()->get();

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
                    'payment_status' => $bill->payment_status,
                    'qr_expiration' => $bill->qr_expiration,
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
                    'payment_status' => $bill->payment_status,
                    'qr_expiration' => $bill->qr_expiration,
                    'created_at' => $bill->created_at,
                    'updated_at' => $bill->updated_at
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
        return 'BILL_' . Str::uuid()->toString();
    }



    public function store(StoreBillRequest $request)
    {

        $openHour = 8;
        $closeHour = 22;

        $currentHour = now()->hour;

        if ($currentHour < $openHour || $currentHour >= $closeHour) {
            return response()->json(['message' => 'Nhà hàng đã đóng cửa. Vui lòng đặt hàng trong khung giờ từ 8h sáng đến 22h tối.'], 400);
        }

        $user = JWTAuth::parseToken()->authenticate();

        $selectedItems = $request->get('cart_items');
        $usePoints = $request->get('use_points', false);
        $vouchers = $request->get('vouchers') ?? null;
        $paymentId = $request->get('payment_id');

        if (empty($selectedItems)) {
            return response()->json(['message' => 'Không có sản phẩm nào được chọn'], 400);
        }

        $cartItems = OnlineCart::where('user_id', $user->id)
            ->whereIn('id', $selectedItems)
            ->with('productDetail')
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['error' => 'Giỏ hàng không tồn tại hoặc đã bị xóa.'], 400);
        }

        try {
            DB::beginTransaction();

            $totalAmount = $this->calculateTotalAmount($cartItems);
            if ($vouchers) {
                $totalAmount = $this->applyVoucher($vouchers, $totalAmount);
            }

            $customer = Customer::where('user_id', $user->id)->first();
            if ($usePoints && $customer) {
                [$totalAmount, $diemtru] = $this->applyPoints($customer, $totalAmount);
            }

            $payment = Cache::remember("payment:{$paymentId}", 60 * 600, function () use ($paymentId) {
                return Payment::find($paymentId);
            });

            if (!$payment) {
                return response()->json(['error' => 'Phương thức thanh toán không hợp lệ.'], 400);
            }

            $paymentStatus = ($payment->name == 'ATM') ? 'pending' : 'paid';
            $qrExpiration = ($payment->name === 'ATM') ? now()->addMinutes(1) : null;

            $bill = Bill::create([
                'ma_bill' => $this->randomMaBill(),
                'user_id' => $user->id,
                'customer_id' => null,
                'user_addresses_id' => $request->get('user_addresses_id'),
                'order_date' => now(),
                'total_amount' => $totalAmount,
                'branch_address' => $request->get('branch_address'),
                'payment_id' => $paymentId,
                // 'voucher_id' => $voucherId,
                'note' => $request->get('note'),
                'order_type' => $request->get('order_type', 'online'),
                'status' => 'pending',
                'payment_status' => $paymentStatus,
                'qr_expiration' => $qrExpiration,
                'table_number' => $request->get('table_number'),
            ]);


            if ($vouchers) {
                $bill->vouchers()->attach($vouchers);
            }


            $billDetails = [];
            $productDetailsToUpdate = [];

            foreach ($cartItems as $cartItem) {
                $billDetails[] = [
                    'bill_id' => $bill->id,
                    'product_detail_id' => $cartItem->product_detail_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->productDetail->sale ?? $cartItem->productDetail->price,
                ];

                if (isset($productDetailsToUpdate[$cartItem->product_detail_id])) {
                    $productDetailsToUpdate[$cartItem->product_detail_id] += $cartItem->quantity;
                } else {
                    $productDetailsToUpdate[$cartItem->product_detail_id] = $cartItem->quantity;
                }
            }

            BillDetail::insert($billDetails);
            foreach ($productDetailsToUpdate as $productDetailId => $quantity) {
                ProductDetail::where('id', $productDetailId)->decrement('quantity', $quantity);
            }

            OnlineCart::where('user_id', $user->id)
                ->whereIn('product_detail_id', $cartItems->pluck('product_detail_id'))
                ->delete();

            DB::commit();
            dispatch(new CheckBillExpiration($bill->id))->delay(now()->addMinutes(1));
            event(new BillCreated($bill));

            return response()->json([
                'message' => 'Đặt hàng thành công',
                'bill' => new BillResource($bill)
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }



    private function calculateTotalAmount($cartItems)
    {
        $totalAmount = 0;
        foreach ($cartItems as $cartItem) {
            $productDetail = $cartItem->productDetail;

            if (!$productDetail || $productDetail->quantity < $cartItem->quantity) {

                throw new Exception('Sản phẩm không tồn tại hoặc không đủ số lượng.');
            }

            $price = $productDetail->sale ?? $productDetail->price;
            $totalAmount += $price * $cartItem->quantity;
        }

        return $totalAmount;
    }


    private function applyVoucher(array $vouchers, $totalAmount)
    {
        $yagiVoucher = null;
        $customerVoucher = null;

        foreach ($vouchers as $voucherId) {
            $voucher = Voucher::find($voucherId);

            if (
                $voucher &&
                $voucher->status &&
                $voucher->start_date <= now() &&
                $voucher->end_date >= now() &&
                $voucher->quantity > 0
            ) {
                if ($voucher->customer_id) {
                    if ($customerVoucher) {
                        return response()->json(['error' => 'Chỉ được chọn một voucher của khách hàng'], 400);
                    }
                    $customerVoucher = $voucher;
                } else {
                    if ($yagiVoucher) {
                        return response()->json(['error' => 'Chỉ được chọn một voucher của Yagi'], 400);
                    }
                    $yagiVoucher = $voucher;
                }
            }
        }

        if ($yagiVoucher) {
            $totalAmount = $this->applyVoucherDiscount($yagiVoucher, $totalAmount);
        }

        if ($customerVoucher) {
            $totalAmount = $this->applyVoucherDiscount($customerVoucher, $totalAmount);
        }

        return max(0, $totalAmount);
    }

    private function applyVoucherDiscount($voucher, $totalAmount)
    {
        if ($voucher->discount_percentage > 0) {
            $discount = min(
                ($totalAmount * $voucher->discount_percentage) / 100,
                $voucher->max_discount_value ?? $totalAmount
            );
        } else {
            $discount = $voucher->value;
        }

        return $totalAmount - $discount;
    }





    private function applyPoints(Customer $customer, $totalAmount)
    {
        $diemtru = 0;
        if ($customer->diemthuong > 0) {
            if ($customer->diemthuong >= $totalAmount) {
                $diemtru = $totalAmount;
                $customer->diemthuong -= $diemtru;
                $totalAmount = 0;
            } else {
                $diemtru = $customer->diemthuong;
                $totalAmount -= $customer->diemthuong;
                $customer->diemthuong = 0;
            }
            $customer->save();
        }
        return [$totalAmount, $diemtru];
    }




    private function handleRefund($bill) {}


    private function notifyUser($bill)
    {
        $user = User::find($bill->user_id);
    }


    public function requestCancelBill(Request $req, $id)
    {
        try {
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

            $hasCancellationHistory = ShippingHistory::where('bill_id', $bill->id)
                ->where('event', 'cancellation_requested')
                ->exists();

            if ($hasCancellationHistory) {
                return response()->json(['message' => 'Bạn không thể yêu cầu hủy lại lần nữa'], 400);
            }

            if ($bill->status == 'pending') {
                $this->cancelBillAndUpdateStock($bill);
                return response()->json(['message' => 'Đơn hàng đã được hủy'], 200);
            }

            DB::beginTransaction();

            $bill->status = 'cancellation_requested';
            $bill->save();

            ShippingHistory::create([
                'bill_id' => $bill->id,
                'user_id' => $user->id,
                'event' => 'cancellation_requested',
                'description' => $req->input('description') ?? 'Yêu cầu hủy đơn hàng',
                'image_url' => null,
            ]);

            DB::commit();

            return response()->json(['message' => 'Yêu cầu hủy đơn hàng đã được gửi'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()], 400);
        }
    }


    private function cancelBillAndUpdateStock($bill)
    {
        $bill->status = 'cancelled';
        $bill->save();

        $billDetails = BillDetail::where('bill_id', $bill->id)->get();

        foreach ($billDetails as $detail) {
            $productDetail = ProductDetail::find($detail->product_detail_id);
            if ($productDetail) {
                $productDetail->quantity += $detail->quantity;
                $productDetail->save();
            }
        }
    }

    public function showBillDetail(string $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $bill = Bill::with(['payment', 'vouchers', 'userAddress', 'user'])
                ->where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$bill) {
                return response()->json(['error' => 'Hóa đơn không tồn tại hoặc không thuộc về người dùng'], 403);
            }

            $billDetails = BillDetail::with(['productDetail.product', 'productDetail.size'])
                ->where('bill_id', $id)
                ->get();

            if ($billDetails->isEmpty()) {
                return response()->json(['error' => 'Chi tiết hóa đơn không tồn tại'], 404);
            }

            return response()->json([
                'bill' => [
                    'id' => $bill->id,
                    'ma_bill' => $bill->ma_bill,
                    'total_amount' => $bill->total_amount,
                    'order_date' => $bill->order_date,
                    'payment_method' => $bill->payment->name ?? null,
                    'address' => $bill->userAddress->address ?? null,
                    'customer' => $bill->user ? [
                        'name' => $bill->user->name ?? $bill->user->gmail,
                        // 'phone' => $bill->user->phone_number,
                    ] : null,
                    'vouchers' => $bill->vouchers->map(function ($voucher) {
                        return [
                            'id' => $voucher->id,
                            'code' => $voucher->code,
                            'name' => $voucher->name,
                            // 'discount' => $voucher->discount,
                        ];
                    }),
                ],
                'bill_details' => BillDetailResource::collection($billDetails),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Chi tiết hóa đơn không tồn tại'], 404);
        }
    }


    public function showShippingHistory(int $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $bill = Bill::with(['payment', 'vouchers', 'userAddress', 'user'])
                ->where('user_id', $user->id)
                ->findOrFail($id);


            if (!$bill) {
                return response()->json(['error' => 'Hóa đơn không tồn tại hoặc không thuộc về người dùng'], 403);
            }

            $billHistory = ShippingHistory::where('bill_id', $bill->id)
                ->with('shipper')
                ->get()
                ->map(function ($res) {
                    return [
                        'event' => $res->event,
                        'description' => $res->description,
                        'created_at' => $res->created_at->toDateTimeString(),
                        'image_url' => $res->image_url,
                        'shipper' => $res->shipper ? [
                            'id' => $res->shipper->id,
                            'name' => $res->shipper->name,
                            'email' => $res->shipper->email,
                            'phone' => $res->shipper->customer ? $res->shipper->customer->phone_number : null,
                        ] : null,
                    ];
                });


            return response()->json([
                'bill' => [
                    'id' => $bill->id,
                    'ma_bill' => $bill->ma_bill,
                    'total_amount' => $bill->total_amount,
                    'order_date' => $bill->order_date,
                    'payment_method' => $bill->payment->name ?? null,
                    'address' => $bill->userAddress->address ?? null,
                    'customer' => $bill->user ? [
                        'name' => $bill->user->name ?? $bill->user->gmail,
                        // 'phone' => $bill->user->customer->phone_number,
                    ] : null,
                    'vouchers' => $bill->vouchers->map(function ($voucher) {
                        return [
                            'id' => $voucher->id,
                            'code' => $voucher->code,
                            'name' => $voucher->name,
                            // 'discount' => $voucher->discount,
                        ];
                    }),
                ],
                'history' => $billHistory,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Chi tiết hóa đơn không tồn tại'], 404);
        }
    }
}
