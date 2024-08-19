<?php

namespace App\Http\Controllers;

use App\Http\Resources\VoucherResource;
use App\Models\Customer;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'point' => 'required',
            'phone_number' => ['required'],
        ]);
        $user = JWTAuth::parseToken()->authenticate();
        $existingCustomer = Customer::where('phone_number', $validatedData['phone_number'])->first();
        if ($existingCustomer && !$existingCustomer->user_id) {
            $existingCustomer::update(["user_id" => $user->id]);
        }
        if ($existingCustomer->user_id !== $user->id) {
            return response()->json([
                "error" => "Số điện thoại không phải là của tài khoản này."
            ], 400);
        }
        if ($request->point > $existingCustomer->diemthuong) {
            return response()->json([
                "error" => "Số điểm không đủ."
            ], 400);
        }
        $voucher = Voucher::create([
            'name' => $request->point,
            "customer_id" => $existingCustomer->id
        ]);

        return response()->json([
            "data" => new VoucherResource($voucher)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $voucher = Voucher::findOrFail($id);
        return response()->json([
            'data' => new VoucherResource($voucher)
        ], 201);
    }
}
