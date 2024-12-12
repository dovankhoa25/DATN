<?php

namespace App\Http\Controllers\Admin;

use App\Events\ItemConfirmedByAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Bill\addTableRequest;
use App\Http\Requests\Bill\BillRequest;
use App\Http\Requests\Bill\FilterBillRequest;
use App\Http\Requests\Bill\ItemBillActiveRequest;
use App\Http\Requests\Bill\removedTableRequest;
use App\Http\Requests\Bill\UpdateBillRequest;
use App\Http\Resources\BillResource;
use App\Models\Bill;
use App\Models\BillDetail;
use App\Models\ShippingHistory;
use App\Models\Table;
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
            return Storage::url($filePath);
        }

        return null;
    }



    public function update(UpdateBillRequest $request, int $id)
    {
        try {

            $user = JWTAuth::parseToken()->authenticate();

            if (
                ($request->input('status') === 'completed' || $request->input('status') === 'failed') &&
                !$user->roles->contains('name', 'admin')
            ) {
                return response()->json(['error' => 'Chỉ admin mới có thể cập nhật trạng thái thành completed hoặc failed.'], 403);
            }

            $bill = Bill::findOrFail($id);

            $newStatus = $request->input('status');
            $image = $request->file('image_url') ? $this->storeImage($request->file('image_url'), 'shipping') : null;

            if ($bill->status === 'shipping' && ($newStatus === 'completed' || $newStatus === 'failed')) {
                $shippingHistory = ShippingHistory::where('bill_id', $bill->id)
                    ->where('event', 'shipping_started')
                    ->latest()
                    ->first();
                if ($shippingHistory) {
                    $shipper = $shippingHistory->shipper_id;
                } else {
                    return response()->json(['error' => 'Không tìm thấy thông tin shipper cho hóa đơn này.'], 404);
                }
            } else {
                $shippers = User::whereHas('roles', function ($query) {
                    $query->where('name', 'shipper');
                })->withCount(['bills' => function ($query) {
                    $query->where('status', 'shipping');
                }])->get();
                if ($shippers->isEmpty()) {
                    return response()->json(['error' => 'Không tìm thấy shipper nào.'], 404);
                }

                if ($shippers->pluck('bills_count')->unique()->count() === 1) {
                    $shipper = $shippers->random()->id;
                } else {
                    $shipper = $shippers->sortBy('bills_count')->first()->id;
                }
            }

            if (!$shipper) {
                return response()->json(['error' => 'Không tìm thấy shipper hợp lệ.'], 400);
            }
            $statusUpdated = false;

            $this->validateStatusTransition($bill, $newStatus);
            $this->handleSpecialStatuses($bill, $newStatus, $user->id, $shipper, $request->input('description') ?? null, $image, $statusUpdated);

            if (!$statusUpdated) {
                $bill->status = $newStatus;
            }

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
            'shipping' => ['completed', 'failed'],
            'completed' => null,
            'failed' => null,
        ];

        $currentStatus = $bill->status;
        $allowedNextStatuses = $validStatuses[$currentStatus] ?? null;


        if (is_array($allowedNextStatuses)) {
            if (!in_array($newStatus, $allowedNextStatuses)) {
                throw new \Exception("Không thể chuyển trạng thái từ {$currentStatus} thành {$newStatus}.");
            }
        } elseif ($allowedNextStatuses !== $newStatus) {
            throw new \Exception("Không thể chuyển trạng thái từ {$currentStatus} thành {$newStatus}.");
        }
        // if ($currentStatus === 'cancellation_requested') {
        //     if (!in_array($newStatus, ['cancellation_approved', 'cancellation_rejected'])) {
        //         throw new \Exception("Trạng thái không hợp lệ. Bạn chỉ có thể cập nhật từ 'cancellation_requested' đến 'cancellation_approved' hoặc 'cancellation_rejected'.");
        //     }
        // } else {
        //     if (!array_key_exists($currentStatus, $validStatuses) || $validStatuses[$currentStatus] !== $newStatus) {
        //         throw new \Exception("Trạng thái không hợp lệ. Bạn chỉ có thể cập nhật từ '{$currentStatus}' đến '{$validStatuses[$currentStatus]}'");
        //     }
        // }

        if (in_array($currentStatus, ['completed', 'failed'])) {
            throw new \Exception('Không thể cập nhật khi trạng thái đã là completed hoặc failed.');
        }

        if ($bill->order_type !== 'online') {
            throw new \Exception('Chỉ có thể cập nhật trạng thái cho đơn hàng online.');
        }

        if (in_array($bill->payment_status, ['pending', 'failed', 'refunded'])) {
            throw new \Exception('Đơn hàng này đang đợi thanh toán không được phép cập nhật.');
        }
    }

    private function handleSpecialStatuses($bill, $newStatus, $userId, $shipper, $description, $image, &$statusUpdated)
    {

        if ($newStatus === 'shipping') {
            $this->createShippingHistory(
                $bill,
                $userId,
                $shipper,
                'shipping_started',
                $description ?? 'Giao hàng',
                $image
            );
        }


        if ($bill->status === 'cancellation_requested') {
            if (in_array($newStatus, ['cancellation_approved', 'cancellation_rejected'])) {
                $event = $newStatus;
                $description = $newStatus === 'cancellation_approved'
                    ? 'Chấp nhận hủy đơn hàng'
                    : 'Hủy thất bại đơn hàng quay lại trạng thái chuẩn bị';

                $this->createShippingHistory($bill, $userId, $shipper, $event, $description, $image);

                if ($newStatus === 'cancellation_rejected') {
                    Log::info('Trạng thái bị từ chối, cập nhật lại trạng thái hóa đơn thành preparing');
                    $bill->status = 'preparing';
                    $bill->save();
                    $statusUpdated = true;
                }
                $this->createShippingHistory($bill, $userId, $shipper, $event, 'đơn hàng đang được chuẩn bị để tiếp tục vận chuyển ', $image);
            }
        }

        if ($bill->status === 'shipping') {
            if ($newStatus === 'completed') {
                $this->createShippingHistory(
                    $bill,
                    $userId,
                    $shipper,
                    'delivered',
                    'Admin xác nhận đơn hàng Lí do : ' . ($description ?? 'Không có lý do'),
                    $image
                );
                $bill->status = 'completed';
                $bill->payment_status = 'successful';
                $bill->save();
                $statusUpdated = true;
            }
        }

        if ($bill->status === 'shipping') {
            if ($newStatus === 'failed') {
                $this->createShippingHistory(
                    $bill,
                    $userId,
                    $shipper,
                    'delivered',
                    'Admin xác nhận hủy đơn hàng Lí do : ' . ($description ?? 'Không có lý do'),
                    $image
                );
            }
            $bill->payment_status = 'refunded';
            $bill->status = 'failed';
            $bill->save();
            $statusUpdated = true;
        }
    }

    private function createShippingHistory($bill, $userId, $shiper, $event, $description, $image)
    {

        Log::info('Creating Shipping History: ', [
            'bill_id' => $bill->id,
            'admin_id' => $userId,
            'shipper_id' => $shiper,
            'event' => $event,
            'description' => $description,
            'image_url' => $image,
        ]);

        ShippingHistory::create([
            'bill_id' => $bill->id,
            'admin_id' => $userId,
            'shipper_id' => $shiper,
            'event' => $event,
            'description' => $description ?? 'Không có mô tả',
            'image_url' => $image,
        ]);
    }







    private function randomMaBill()
    {
        return 'BILL_' . Str::uuid()->toString();
    }

    public function getBillByTableNumber(int $tableNumber)
    {
        // Lấy dữ liệu bill kèm theo các quan hệ billDetails, productDetail, product, size và images load quan hệ
        $bills = Bill::with(
            'billDetails.productDetail.product',
            'billDetails.productDetail.size',
            'billDetails.productDetail.images',
            'tables'
        )
            ->whereHas('tables', function ($query) use ($tableNumber) {
                $query->where('tables.id', $tableNumber);
            })
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
                'tables' => $bill->tables->map(function ($table) {
                    return [
                        'id' => $table->id,
                        'table' => $table->table,
                    ];
                }),
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




    function addTableGroupToBill(addTableRequest $req)
    {
        $tableIds = $req->input('tableIds');
        $billId = $req->input('bill_id');

        $bill = Bill::find($billId);

        if (!$bill) {
            return response()->json(['message' => 'Hóa đơn không tồn tại'], 404);
        }

        if ($bill->status !== 'pending') {
            return response()->json(['message' => 'Hóa đơn không cho phép thêm bàn'], 400);
        }

        $existingTableIds = $bill->tables()->pluck('tables.id')->toArray();
        $newTableIds = array_diff($tableIds, $existingTableIds);

        $invalidTables = Table::whereIn('id', $newTableIds)
            ->where('reservation_status', '!=', 'close')
            ->pluck('id')
            ->toArray();

        if (!empty($invalidTables)) {
            return response()->json([
                'message' => 'Một số bàn đang có khách vui lòng chọn lại',
                'table_false' => $invalidTables,
            ], 400);
        }

        DB::beginTransaction();
        try {
            $bill->tables()->attach($newTableIds);
            Table::whereIn('id', $newTableIds)->update(['reservation_status' => 'open']);
            DB::commit();

            return response()->json([
                'message' => 'Thêm bàn vào hóa đơn thành công',
                'addedTableIds' => $newTableIds,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Đã xảy ra lỗi khi thêm bàn vào hóa đơn',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function removedTableFromBill(removedTableRequest $req)
    {
        $tableIds = $req->input('tableIds');
        $billId = $req->input('bill_id');

        $bill = Bill::find($billId);

        if (!$bill) {
            return response()->json(['message' => 'Hóa đơn không tồn tại'], 404);
        }

        $existingTableIds = $bill->tables()->pluck('tables.id')->toArray();

        $invalidTableIds = array_diff($tableIds, $existingTableIds);

        if (!empty($invalidTableIds)) {
            return response()->json([
                'message' => 'Một số bàn không thuộc hóa đơn này',
                'invalid_table_ids' => $invalidTableIds,
            ], 400);
        }

        $remainingTables = count($existingTableIds) - count($tableIds);
        if ($remainingTables <= 0) {
            return response()->json([
                'message' => 'Không thể xóa tất cả các bàn khỏi hóa đơn. Cần giữ ít nhất một bàn.',
            ], 400);
        }

        DB::beginTransaction();
        try {
            $bill->tables()->detach($tableIds);

            Table::whereIn('id', $tableIds)->update(['reservation_status' => 'close']);
            DB::commit();

            return response()->json([
                'message' => 'Các bàn đã được xóa khỏi hóa đơn và đổi trạng thái thành close',
                'removed_table_ids' => $tableIds,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Đã xảy ra lỗi khi thêm bàn vào hóa đơn',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function showShippingHistory(int $id)
    {
        try {

            $bill = Bill::with(['payment', 'vouchers', 'userAddress', 'user'])
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
            return response()->json(['error' => 'Chi tiết lịch sử vận chuyển đơn hàng'], 404);
        }
    }
}
