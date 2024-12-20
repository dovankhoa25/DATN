<?php

namespace App\Http\Controllers\Client;

use App\Events\ItemAddedToBill;
use App\Events\ItemCancelledFromBill;
use App\Http\Controllers\Controller;
use App\Http\Requests\Bill\Client\BillOderRequest;
use App\Http\Requests\Bill\Client\ItemBillRequest;
use App\Http\Resources\Client\BillOrderResource;
use App\Jobs\CheckBillExpiration;
use App\Models\Bill;
use App\Models\BillDetail;
use App\Models\Customer;
use App\Models\OrderCart;
use App\Models\Payment;
use App\Models\Table;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BillOrderController extends Controller
{
    public function getBillOnline(BillOderRequest $request)
    {
        $bill = Bill::where('ma_bill', $request->ma_bill)
            ->where('order_type', 'in_restaurant')
            ->where('status', 'pending')
            ->first();

        if ($bill) {
            $tables = $bill->tables;
            return new BillOrderResource($bill, $tables);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Bill not found.',
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
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();


        BillDetail::insert($billDetailsData);

        OrderCart::whereIn('id', $request->id_order_cart)->delete();

        broadcast(new ItemAddedToBill([
            'bill_id' => $bill->id,
            'items' => $billDetailsData
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Các sản phẩm đã được thêm vào hóa đơn thành công.',
        ]);
    }

    public function cancelItem(Request $request)
    {

        $request->validate([
            'ma_bill' => 'required|string|exists:bills,ma_bill',
            'id_bill_details' => 'array|required|exists:bill_details,id',
        ]);

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

        $billDetails = BillDetail::where('bill_id', $bill->id)
            ->where('status', 'pending')
            ->whereIn('id', $request->id_bill_details)
            ->get();

        if ($billDetails->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không có sản phẩm nào có thể hủy trong hóa đơn.',
            ], 404);
        }

        BillDetail::whereIn('id', $billDetails->pluck('id'))
            ->update(['status' => 'cancelled', 'updated_at' => now()]);

        broadcast(new ItemCancelledFromBill([
            'bill_id' => $bill->id,
            'cancelled_items' => $billDetails->pluck('id'),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Các sản phẩm đã được hủy thành công.',
        ]);
    }


    public function saveBill(BillOderRequest $request)
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

        if ($bill->billDetails()->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'chưa ăn chưa gọi món mà thanh toán là sao vậy , gọi mon đi.',
            ], 400);
        }


        $phone = $request->get('phone');
        $paymentId = $request->get('payment_id');
        $voucher = $request->get('voucher');
        $note = $request->get('note');

        $voucherId = null;
        if ($voucher) {
            $voucher = Voucher::where('code', $voucher)->first();
            $voucherId = $voucher->id;
        }

        if ($voucher && ($voucher->quantity < 1 || $voucher->start_date > now() || $voucher->end_date < now())) {
            return response()->json(['error' => 'Voucher đã hết hoặc chưa được phép dùng.'], 400);
        }

        try {
            DB::beginTransaction();

            $customerId = null;
            if ($phone) {
                $customer = Customer::where('phone_number', $phone)->first();
                if (!$customer) {
                    $customer = Customer::create([
                        'phone_number' => $phone,
                        'name' => $request->get('name', 'Khách hàng'),
                        'email' => $request->get('email'),
                    ]);
                }
                $customerId = $customer->id;
            }



            $totalAmount = $bill->total_amount;
            $totalAmount = $this->applyVoucher($voucherId, $totalAmount);
            if ($totalAmount) {
                $bill->total_amount = $totalAmount;
            }



            $payment = Cache::remember("payment:{$paymentId}", 60 * 600, function () use ($paymentId) {
                return Payment::find($paymentId);
            });

            if (!$payment) {
                return response()->json(['error' => 'Phương thức thanh toán không hợp lệ.'], 400);
            }
            $tableIds = $bill->tables()->pluck('tables.id');


            $paymentStatus = ($payment->id == 2) ? 'pending' : 'successful';
            $qrExpiration = ($payment->id === 2) ? now()->addMinutes(30) : null;
            $bill->customer_id = $customerId;
            $bill->payment_id = $paymentId;
            // $bill->voucher_id = $voucherId;
            $bill->note = $note;
            $bill->branch_address = 'FPT Poly';
            $bill->payment_status = $paymentStatus;
            $bill->status = 'completed';
            $bill->qr_expiration = $qrExpiration;
            $bill->save();

            Table::whereIn('id', $tableIds)->update(['reservation_status' => 'close']);

            DB::commit();

            dispatch(new CheckBillExpiration($bill->id))->delay(now()->addMinutes(30));
            return response()->json([
                'message' => 'Đặt hàng thành công',
                'bill' => new BillOrderResource($bill)
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


    private function applyVoucher($voucherId, $totalAmount)
    {
        $voucher = Voucher::find($voucherId);

        if (
            $voucher && $voucher->status &&
            $voucher->start_date <= now() &&
            $voucher->end_date >= now() &&
            $voucher->quantity > 0
        ) {

            $totalAmount -= $voucher->value;

            $voucher->decrement('quantity');

            return max(0, $totalAmount);
        }

        return $totalAmount;
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
}
