<?php

namespace App\Http\Controllers\Admin;

use App\Events\ItemConfirmedByAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Bill\BillRequest;
use App\Http\Requests\Bill\FilterBillRequest;
use App\Http\Requests\Bill\ItemBillActiveRequest;
use App\Http\Requests\Bill\UpdateBillRequest;
use App\Http\Resources\BillResource;
use App\Models\Bill;
use App\Models\BillDetail;
use App\Models\ShippingHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

class BillController extends Controller
{

    public function index(FilterBillRequest $request)
    {

        $perPage = $request['per_page'] ?? 10;
        $bills = Bill::filter($request->filters())->latest()->paginate($perPage);
        return BillResource::collection($bills);
    }


    public function store(BillRequest $request)
    {
        $validatedData = $request->validated();


        if ($request->input('order_type') == 'in_restaurant') {
            $validatedData['table_number'] = $request->input('table_number');
            $validatedData['branch_address'] = $request->input('branch_address');
            $validatedData['user_addresses_id'] = null;
        } else {
            $validatedData['user_addresses_id'] = $request->input('user_addresses_id');
            $validatedData['table_number'] = null;
            $validatedData['branch_address'] = null;
        }

        $bill = Bill::create($validatedData);

        return response()->json([
            'message' => 'Bill ok',
            'bill' => $bill
        ], 201);
    }



    public function show(string $id)
    {
        try {
            $bill = Bill::findOrFail($id);
            return response()->json([
                'bill' => new BillResource($bill),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'bill không tồn tại'], 404);
        }
    }

    protected function storeImage($file, $directory)
    {
        if ($file) {
            $filePath = $file->store($directory, 'public');
            return Storage::url($filePath); // Trả về URL công khai
        }

        return null;
    }



    public function update(UpdateBillRequest $request, int $id)
    {
        try {

            $user = JWTAuth::parseToken()->authenticate();
            $bill = Bill::findOrFail($id);

            $newStatus = $request->input('status');
            $image = $request->file('image_url') ? $this->storeImage($request->file('image_url'), 'shipping') : null;
            if (!$request->shiper_id) {
                $shippers = User::whereHas('roles', function ($query) {
                    $query->where('name', 'shipper');
                })->withCount(['bills' => function ($query) {
                    $query->where('status', 'shipping');
                }])->orderBy('bills_count', 'asc')
                    ->first();

                if (!$shippers) {
                    return response()->json(['error' => 'Không tìm thấy shipper nào.'], 404);
                }

                $shipper = $shippers->id;
            } else {
                $shipper = $request->shiper_id;
            }
            $this->validateStatusTransition($bill, $newStatus);
            $this->handleSpecialStatuses($bill, $newStatus, $user->id, $shipper, $request->input('description'), $image);

            $bill->status = $newStatus;
            $bill->save();

            return response()->json([
                'message' => 'Cập nhật trạng thái thành công.',
                'data' => new BillResource($bill),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Không tìm thấy hóa đơn.'], 404);
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật hóa đơn', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Đã xảy ra lỗi: ' . $e->getMessage()], 500);
        }
    }

    private function validateStatusTransition($bill, $newStatus)
    {
        $validStatuses = [
            'pending' => 'confirmed',
            'confirmed' => 'preparing',
            'preparing' => 'shipping',
            'shipping' => 'completed',
            'completed' => null,
            'failed' => null,
        ];

        $currentStatus = $bill->status;

        if (!array_key_exists($currentStatus, $validStatuses) || $validStatuses[$currentStatus] !== $newStatus) {
            throw new \Exception("Trạng thái không hợp lệ. Bạn chỉ có thể cập nhật từ '{$currentStatus}' đến '{$validStatuses[$currentStatus]}'");
        }

        if (in_array($currentStatus, ['completed', 'failed'])) {
            throw new \Exception('Không thể cập nhật khi trạng thái đã là completed hoặc failed.');
        }

        if ($bill->order_type !== 'online') {
            throw new \Exception('Chỉ có thể cập nhật trạng thái cho đơn hàng online.');
        }

        if (in_array($bill->payment_status, ['pending', 'failed', 'refunded'])) {
            throw new \Exception('Đơn hàng này không được phép cập nhật.');
        }
    }

    private function handleSpecialStatuses($bill, $newStatus, $userId, $shiper, $description, $image)
    {
        if ($newStatus === 'shipping') {
            $this->createShippingHistory($bill, $userId, $shiper, 'shipping_started', $description ?? 'Giao hàng', $image);
        }

        if ($bill->status === 'cancellation_requested') {
            if (in_array($newStatus, ['cancellation_approved', 'cancellation_rejected'])) {
                $event = $newStatus === 'cancellation_approved' ? 'cancellation_approved' : 'cancellation_rejected';
                $description = $newStatus === 'cancellation_approved'
                    ? 'Chấp nhận hủy đơn hàng'
                    : 'Hủy thất bại đơn hàng quay lại trạng thái chuẩn bị';
                $this->createShippingHistory($bill, $userId, $shiper, $event, $description, $image);
            }

            if ($newStatus === 'cancellation_rejected') {
                $bill->status = 'preparing';
            }
        }
    }


    private function createShippingHistory($bill, $userId, $shiper, $event, $description, $image)
    {
        ShippingHistory::create([
            'bill_id' => $bill->id,
            'admin_id' => $userId,
            'shipper_id' => $shiper,
            'event' => $event,
            'description' => $description ?? 'Không có mô tả',
            'image_url' => $this->storeImage($image, 'shipping') ?? null,
        ]);
    }




    public function updateShippingStatus(Request $request, string $id)
    {
        try {

            $user = JWTAuth::parseToken()->authenticate();

            $request->validate([
                'status' => 'required|in:shipping_started,pending_retry,delivered,delivery_failed',
                'description' => 'nullable|string',
                'image_url' => 'nullable|url',
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
                } elseif ($status == 'delivery_failed') {
                    $bill->status = 'pending_retry';
                    ShippingHistory::create([
                        'bill_id' => $bill->id,
                        'user_id' => $user->id,
                        'event' => $status,
                        'description' => $request->input('description') ?? 'giao hàng không thành công',
                        'image_url' => $request->input('image_url') ?  $this->storeImage($request->file('image_url'), 'shipping') : null,
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



    private function randomMaBill()
    {
        return 'BILL_' . Str::uuid()->toString();
    }

    public function getBillByTableNumber(int $tableNumber)
    {
        // Lấy dữ liệu bill kèm theo các quan hệ billDetails, productDetail, product, size và images load quan hệ
        $bills = Bill::with('billDetails.productDetail.product', 'billDetails.productDetail.size', 'billDetails.productDetail.images')
            ->where('table_number', $tableNumber)
            ->where('status', 'pending')
            ->where('order_type', 'in_restaurant')
            ->get();

        // lọc cust
        $formattedBills = $bills->map(function ($bill) {
            $billDetails = $bill->billDetails->map(function ($detail) {
                return [
                    'id_bill_detail' => $detail->id,
                    'quantity' => $detail->quantity,
                    'product' => [
                        'id' => $detail->productDetail->product->id,
                        'name' => $detail->productDetail->product->name,
                        'thumbnail' => $detail->productDetail->product->thumbnail,
                        'description' => $detail->productDetail->product->description,
                        // 'category_id' => $detail->productDetail->product->category_id,
                        'product_detail' => [
                            'id' => $detail->productDetail->id,
                            'price' => $detail->productDetail->sale ?? $detail->productDetail->price,
                            'size' => [
                                'id' => $detail->productDetail->size->id,
                                'name' => $detail->productDetail->size->name,
                            ],
                            'images' => $detail->productDetail->images->map(function ($image) {
                                return [
                                    'id' => $image->id,
                                    'name' => $image->name,
                                ];
                            }),
                        ],
                        'status' => $detail->status,
                    ],
                ];
            });

            return [
                'id' => $bill->id,
                'ma_bill' => $bill->ma_bill,
                'order_date' => $bill->order_date,
                'total_amount' => $bill->total_amount,
                'table_number' => $bill->table_number,
                'status' => $bill->status,
                'order_type' => $bill->order_type,
                'bill_details' => $billDetails,
            ];
        });


        return response()->json([
            'data' => $formattedBills,
            'total_bills' => $bills->count(),
        ], 200);
    }


    public function activeItems(ItemBillActiveRequest $request)
    {
        $ids = $request->get('id_billdetails');

        if (empty($ids) || !is_array($ids)) {
            return response()->json([
                'message' => 'Danh sách món ăn không hợp lệ.',
            ], 400);
        }

        $details = BillDetail::whereIn('id', $ids)->get();

        if ($details->isEmpty()) {
            return response()->json([
                'message' => 'Không tìm thấy món ăn nào trong danh sách.',
            ], 404);
        }

        $bill = $details->first()->bill;

        if ($bill->status !== 'pending') {
            return response()->json([
                'message' => 'Hóa đơn này đã hoàn thành xử lí.',
            ], 400);
        }

        try {
            DB::beginTransaction();

            $totalAmount = 0;

            foreach ($details as $detail) {
                if ($detail->status === 0) {
                    $detail->status = 1;
                    $detail->save();

                    $totalAmount += $detail->price * $detail->quantity;
                }
            }

            $bill->total_amount += $totalAmount;
            $bill->save();

            DB::commit();
            broadcast(new ItemConfirmedByAdmin($detail));
            return response()->json([
                'message' => 'Tất cả món ăn đã được cập nhật trạng thái.',
                'data' => [
                    'details' => $details,
                    'bill' => $bill,
                ],
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
