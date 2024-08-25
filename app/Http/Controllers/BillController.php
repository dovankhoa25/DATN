<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\BillRequest;
use App\Http\Resources\BillResource;
use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bills = Bill::paginate(10);
        return BillResource::collection($bills);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BillRequest $request)
    {
        try {
            $bill = Bill::create([
                'ma_bill' => $this->randomMaBill(),
                'user_id' => $request->get('user_id'),
                'order_date' => $request->get('order_date'),
                'total_money' => $request->get('total_money'),
                'address' => $request->get('address'),
                'payment_id' => $request->get('payment_id'),
                'voucher_id' => $request->get('voucher_id'),
                'note' => $request->get('note'),
                'status' => 'pending',
            ]);
            return response()->json([
                'data' => new BillResource($bill),
                'message' => 'success'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'thêm bill thất bại'], 404);
        }
    }

    /**
     * Display the specified resource.
     */
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,completed',
            ]);

            $bill = Bill::findOrFail($id);

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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    private function randomMaBill()
    {
        do {
            $maBill = strtoupper(Str::random(10));
            $exists = Bill::where('ma_bill', $maBill)->exists();
        } while ($exists);

        return $maBill;
    }
}
