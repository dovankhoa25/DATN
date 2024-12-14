<?php

namespace App\Http\Controllers\Shipper;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shipper\FilterBillRequest;
use App\Http\Resources\BillResource;
use App\Http\Resources\Shipper\BillCollection;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\ShippingHistory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class ShipperController extends Controller
{

    public function listBill(FilterBillRequest $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $perPage = $request->input('per_page', 10);

            $bills = Bill::filter($request->all())
                ->latest()
                ->where('shiper_id', $user->id)
                ->orderBy('created_at', 'desc')
                // ->with(['shippingHistories.admin', 'shippingHistories.shipper', 'shipper.customer'])
                ->with([
                    'shippingHistories.admin',
                    'shippingHistories.shipper',
                    'shipper.customer',
                    'userAddress',
                    'vouchers',
                    'tables',
                ])
                ->paginate($perPage);

            return new BillCollection($bills);
        } catch (\Exception $e) {
            Log::error('Lỗi lấy danh sách hóa đơn', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Đã xảy ra lỗi.'], 500);
        }
    }


    public function updateShippingStatus(Request $request, string $id)
    {
        try {

            $user = JWTAuth::parseToken()->authenticate();

            $request->validate([
                'status' => 'required|in:shipping_started,pending_retry,delivered,delivery_failed',
                'description' => 'nullable|string',
                'image_url' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $bill = Bill::findOrFail($id);

            $status = $request->input('status');

            if ($bill->status == 'pending_retry' && $status !== 'shipping_started') {
                throw new \Exception('Trạng thái không hợp lệ. Bạn cần chuyển sang vận chuyển lại trước.');
            }

            if ($bill->status == 'completed' || $bill->status == 'failed') {
                return response()->json(['error' => 'Không thể thay đổi trạng thái khi đơn hàng đã hoàn thành hoặc thất bại'], 400);
            }

            ShippingHistory::create([
                'bill_id' => $bill->id,
                'user_id' => $user->id,
                'event' => $status,
                'description' => $request->input('description') ?? null,
                'image_url' => $request->input('image_url') ?  $this->storeImage($request->file('image_url'), 'shipping') : null,
            ]);

            if ($bill->status == 'shipping') {
                if ($status == 'delivered') {
                    $bill->status = 'completed';
                    $bill->payment_status = 'successful';

                    $customer = Customer::where("user_id", $bill->user_id)->first();
                    if ($customer) {
                        $customer->diemthuong += ($bill->total_amount / 1000);
                        $customer->save();
                    } else {
                        Log::warning('Không tìm thấy khách hàng để tích điểm.', ['user_id' => $bill->user_id]);
                    }

                    ShippingHistory::create([
                        'bill_id' => $bill->id,
                        'user_id' => $user->id,
                        'event' => $status,
                        'description' => $request->input('description') ?? 'giao hàng thành công',
                        'image_url' => $request->input('image_url') ?  $this->storeImage($request->file('image_url'), 'shipping') : null,
                    ]);
                } elseif ($status == 'delivery_failed') {
                    $failedCount = ShippingHistory::where('bill_id', $bill->id)
                        ->where('event', 'delivery_failed')
                        ->count();

                    if ($failedCount >= 2) {
                        $bill->status = 'failed';
                        $bill->payment_status = 'failed';
                    } else {
                        $bill->status = 'pending_retry';
                    }

                    ShippingHistory::create([
                        'bill_id' => $bill->id,
                        'user_id' => $user->id,
                        'event' => $status,
                        'description' => $request->input('description') ?? 'Giao hàng không thành công',
                        'image_url' => $request->input('image_url') ? $this->storeImage($request->file('image_url'), 'shipping') : null,
                    ]);
                }
            }

            $bill->save();

            return response()->json([
                'message' => 'Trạng thái giao hàng đã được cập nhật',
                'data' => new BillResource($bill)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Không tìm thấy đơn hàng'], 404);
        }
    }




    public function retryShipping(Request $request, string $id)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $bill = Bill::findOrFail($id);

            if ($bill->status !== 'pending_retry') {
                return response()->json(['error' => 'Chỉ có thể thử lại khi trạng thái là pending_retry'], 400);
            }

            $bill->status = 'shipping_started';

            ShippingHistory::create([
                'bill_id' => $bill->id,
                'user_id' => $user->id,
                'event' => 'shipping_started',
                'description' => 'Bắt đầu vận chuyển lại',
            ]);

            $bill->save();

            return response()->json(['message' => 'Đã chuyển trạng thái sang vận chuyển lại', 'data' => new BillResource($bill)]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Không tìm thấy đơn hàng'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Đã xảy ra lỗi: ' . $e->getMessage()], 500);
        }
    }
}
