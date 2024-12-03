<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Voucher\FilterVoucherRequest;
use App\Http\Requests\Voucher\VoucherRequest;
use App\Http\Resources\VoucherResource;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class VoucherController extends Controller
{

    public function index(FilterVoucherRequest $request)
    {
        try {
            $perPage = $request->get('per_page', 10);

            $filters = $request->all();

            $vouchers = Voucher::filter($filters);

            $paginated = $vouchers->paginate($perPage);

            return VoucherResource::collection($paginated);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Không tìm thấy voucher'], 404);
        }
    }




    public function store(VoucherRequest $request)
    {
        $data = $request->only([
            'name',
            'value',
            'discount_percentage',
            'max_discount_value',
            'image',
            'start_date',
            'end_date',
            'status',
            'customer_id',
            'quantity'

        ]);
        // Kiểm tra và tự động cập nhật max_discount_value
        if ($data['discount_percentage'] == 0) {
            $data['max_discount_value'] = 0;
        } else {
            $data['value'] = 0;
        }

        $voucher = Voucher::create($data);

        return response()->json([
            'message' => 'Thêm Mới Thành Công!',
            'data' => $voucher
        ], 201);
    }



    public function show($id)
    {
        $voucher = Voucher::findOrFail($id);
        return response()->json([
            'data' => new VoucherResource($voucher)
        ], 201);
    }

    public function update(VoucherRequest $request, $id)
    {

        $voucher = Voucher::findOrFail($id);
        $data = $request->only([
            'name',
            'value',
            'discount_percentage',
            'max_discount_value',
            'image',
            'start_date',
            'end_date',
            'status',
            'customer_id',
            'quantity'

        ]);
        if ($data['discount_percentage'] == 0) {
            $data['max_discount_value'] = 0;
        }
        $voucher->update($data);

        return response()->json([
            'message' => 'Thêm Mới Thành Công!',
            'data' => $voucher
        ], 200);
    }
}
