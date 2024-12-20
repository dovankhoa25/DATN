<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Voucher\FilterVoucherRequest;
use App\Http\Requests\Voucher\VoucherRequest;
use App\Http\Resources\VoucherResource;
use App\Models\Bill;
use App\Models\BillVoucher;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VoucherController extends Controller
{

    public function index(FilterVoucherRequest $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $filters = $request->all();

            $vouchers = Voucher::filter($filters)
                ->latest()
                ->paginate($perPage);

            return VoucherResource::collection($vouchers);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Không tìm thấy voucher'], 404);
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

    public function store(VoucherRequest $request)
    {


        $image = $this->storeImage($request->file('image'), 'voucher');

        $data = $request->only([
            'name',
            'value',
            'discount_percentage',
            'max_discount_value',
            'start_date',
            'end_date',
            'status',
            'customer_id',
            'quantity'
        ]);

        $data['image'] = $image;

        $voucherCode = Str::upper(Str::random(10));

        while (Voucher::where('code', $voucherCode)->exists()) {
            $voucherCode = Str::upper(Str::random(10));
        }

        $data['code'] = $voucherCode;


        if ($data['discount_percentage'] > 0) {
            $data['value'] = 0;
        } elseif ($data['value'] > 0) {
            $data['discount_percentage'] = 0;
            $data['max_discount_value'] = 0;
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
        $billVoucher = BillVoucher::where('voucher_id', $id)->first();

        if ($billVoucher) {
            return response()->json([
                'message' => 'Voucher đã được sử dụng không thể sửa.',
            ], 404);
        }

        if ($request->hasFile('image')) {
            if ($voucher->image) {
                $oldImagePath = str_replace('/storage/', '', $voucher->image);
                Storage::disk('public')->delete($oldImagePath);
            }

            $imagePath = $this->storeImage($request->file('image'), 'voucher');
            $data['image'] = $imagePath;
        }

        $data = $request->only([
            'name',
            'value',
            'discount_percentage',
            'max_discount_value',
            'start_date',
            'end_date',
            'status',
            'customer_id',
            'quantity',
        ]);

        if ($data['discount_percentage'] > 0) {
            $data['value'] = 0;
        } elseif ($data['value'] > 0) {
            $data['discount_percentage'] = 0;
            $data['max_discount_value'] = 0;
        }

        $voucher->update($data);

        return response()->json([
            'message' => 'Cập nhật thành công!',
            'data' => $voucher
        ], 200);
    }
}
