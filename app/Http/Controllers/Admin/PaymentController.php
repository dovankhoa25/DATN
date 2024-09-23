<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\FilterPaymentRequest;
use App\Http\Requests\PaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(FilterPaymentRequest $request)
    {
        $perPage = $request['per_page'] ?? 10;
        $listPayment = Payment::filter($request)->paginate($perPage);
        $paymentCollection = PaymentResource::collection($listPayment);
        return $paymentCollection;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaymentRequest $request)
    {
        $paymentData = $request->all();
        $paymentData['status'] = true;
        $payment = Payment::create($paymentData);
        $paymentCollection = new PaymentResource($payment);
        if ($payment) {
            return response()->json($paymentCollection, 201);
        } else {
            return response()->json(['error', 'Thêm hình thức thanh toán thất bại']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $payment = Payment::FindorFail($id);
        $paymentCollection = new PaymentResource($payment);
        if ($payment) {
            return response()->json($paymentCollection, 200);
        } else {
            return response()->json(['error', 'Không tìm thấy size theo id']);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PaymentRequest $request, string $id)
    {
        $payment = Payment::FindorFail($id);
        $paymentData = $request->all();

        $res = $payment->update($paymentData);
        $paymentCollection = new PaymentResource($payment);
        if ($res) {
            return response()->json($paymentCollection, 200);
        } else {
            return response()->json(['error', 'Sửa hình thức thanh toán thất bại']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $payment = Payment::FindorFail($id);
        $payment->delete();

        return response()->json(['message' => 'xóa thành công']);
    }
}
