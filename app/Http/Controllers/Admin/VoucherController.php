<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VoucherRequest;
use App\Http\Resources\VoucherResource;
use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vouchers = Voucher::paginate(10);
        return VoucherResource::collection($vouchers);
    }

  
    // public function store(Request $request)
    // {
    //     $validatedData = $request->validate([
    //         'point' => 'required',
    //         'phone_number' => ['required'],
    //     ]);
    //     $user = JWTAuth::parseToken()->authenticate();
    //     $existingCustomer = Customer::where('phone_number', $validatedData['phone_number'])->first();
    //     if ($existingCustomer && !$existingCustomer->user_id) {
    //         $existingCustomer::update(["user_id" => $user->id]);
    //     }
    //     if ($existingCustomer->user_id !== $user->id) {
    //         return response()->json([
    //             "error" => "Số điện thoại không phải là của tài khoản này."
    //         ], 400);
    //     }
    //     if ($request->point > $existingCustomer->diemthuong) {
    //         return response()->json([
    //             "error" => "Số điểm không đủ."
    //         ], 400);
    //     }
    //     $voucher = Voucher::create([
    //         'name' => $request->point,
    //         "customer_id" => $existingCustomer->id
    //     ]);

    //     return response()->json([
    //         "data" => new VoucherResource($voucher)
    //     ], 201);
    // }


    public function store(VoucherRequest $request)
    {
        // $data = $request->validate([
        //     'name' => 'required|string|max:255',
        //     'value' => 'required|numeric|min:0',
        //     'image' => 'nullable|string',
        //     'status' => 'nullable|boolean',
        //     'customer_id' => 'nullable|exists:customers,id',
        //     'expiration_date' => 'nullable|date|after_or_equal:today',
        // ]);
        $data = $request->validated();
        $voucher = Voucher::create($data);
        
        return response()->json([
            'message' => 'Thêm Mới Thành Công!',
            'data' => $voucher
        ],201);

    }



    public function show($id)
    {
        $voucher = Voucher::findOrFail($id);
        return response()->json([
            'data' => new VoucherResource($voucher)
        ], 201);
    }
}
