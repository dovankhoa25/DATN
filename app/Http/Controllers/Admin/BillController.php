<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bill\BillRequest;
use App\Http\Requests\Bill\FilterBillRequest;
use App\Http\Requests\Bill\ItemBillActiveRequest;
use App\Http\Resources\BillResource;
use App\Models\Bill;
use App\Models\BillDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BillController extends Controller
{

    public function index(FilterBillRequest $request)
    {

        $perPage = $request['per_page'] ?? 10;
        $bills = Bill::filter($request->filters())->paginate($perPage);
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


    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:confirmed,preparing,shipping,completed,failed',
            ]);

            $bill = Bill::findOrFail($id);

            $validStatuses = [
                'pending' => 1,
                'confirmed' => 2,
                'preparing' => 3,
                'shipping' => 4,
                'completed' => 5,
                'failed' => 6
            ];

            $currentStatus = $bill->status;
            $newStatus = $request->input('status');

            if ($bill->order_type !== 'online') {
                return response()->json(['error' => 'Chỉ có thể cập nhật trạng thái cho đơn hàng online'], 400);
            }


            if (in_array($currentStatus, ['completed', 'failed'])) {
                return response()->json(['error' => 'Không thể cập nhật khi trạng thái đã là completed hoặc failed'], 400);
            }

            if ($validStatuses[$newStatus] < $validStatuses[$currentStatus]) {
                return response()->json(['error' => 'Không thể cập nhật trạng ngược lại'], 400);
            }


            $bill->status = $request->input('status');
            $bill->save();

            return response()->json([
                'message' => 'Status updated successfully',
                'data' => new BillResource($bill)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'không tìm thấy bills'], 404);
        }
    }

    private function randomMaBill()
    {
        do {
            $maBill = strtoupper(Str::random(10));
            $exists = Bill::where('ma_bill', $maBill)->exists();
        } while ($exists);

        return $maBill;
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

        // Trả về JSON với dữ liệu đã định dạng
        return response()->json([
            'data' => $formattedBills,
            'total_bills' => $bills->count(), // Số lượng hóa đơn
        ], 200);
    }


    public function activeItem(ItemBillActiveRequest $request)
    {
        $detail_bill = BillDetail::where('id', $request->id_billdetail)
            ->first();


        if (!$detail_bill) {
            return response()->json([
                'message' => 'Không có món ăn nào như vậy trong đây'
            ], 400);
        }

        if ($detail_bill->bill->status !== 'pending') {
            return response()->json([
                'message' => 'Mã bill này đã hoàn thành xử lí',
            ], 400);
        }

        $detail_bill->status = 1;
        $detail_bill->save();

        return response()->json([
            'message' => 'Món ăn đã đang làm',
            'data' =>  $detail_bill,
        ], 200);
    }
}
