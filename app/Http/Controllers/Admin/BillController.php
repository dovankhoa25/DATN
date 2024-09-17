<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bill\FilterBillRequest;
use App\Http\Requests\BillRequest;
use App\Http\Resources\BillResource;
use App\Models\Bill;
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


    public function edit(string $id)
    {
        //
    }

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
