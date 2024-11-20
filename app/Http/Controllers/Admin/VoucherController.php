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
            // \Log::info('Filters applied: ', $filters);

            $vouchers = Voucher::filter($filters);
            // \Log::info('Query result: ', $vouchers->toSql());

            $paginated = $vouchers->paginate($perPage);

            return VoucherResource::collection($paginated);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Không tìm thấy voucher'], 404);
        }
    }




    public function store(VoucherRequest $request)
    {
        $data = $request->only(['name', 'value', 'image', 'start_date', 'end_date', 'status', 'customer_id', 'quantity']);

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
        try {
            $voucher = Voucher::findOrFail($id);

            $data = $request->only(['name', 'value', 'image', 'start_date', 'end_date', 'status', 'customer_id', 'quantity']);

            $voucher->update($data);

            return response()->json([
                'message' => 'Cập Nhật Thành Công!',
                'data' => $voucher
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Không tìm thấy voucher để cập nhật!'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Đã xảy ra lỗi trong quá trình cập nhật.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
