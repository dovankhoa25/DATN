<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BillRequest;
use App\Http\Resources\BillDetailResource;
use App\Models\BillDetail;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BillDetailController extends Controller
{

    // public function index()
    // {
    //     $bills = BillDetail::paginate(10);
    //     return BillDetailResource::collection($bills);
    // }


    // public function show(string $id)
    // {
    //     try {
    //         $billDetail = BillDetail::where('bill_id', $id)->firstOrFail();
    //         return response()->json([
    //             'data' => new BillDetailResource($billDetail),
    //         ], 200);
    //     } catch (ModelNotFoundException $e) {
    //         return response()->json(['error' => 'Chi tiết hoá đơn không tồn tại'], 404);
    //     }
    // }

    public function show(string $id)
    {
        try {
           
            $billDetails = BillDetail::with(['productDetail.product'])
                ->where('bill_id', $id)
                ->get();
            
            if ($billDetails->isEmpty()) {
                return response()->json(['error' => 'Chi tiết hóa đơn không tồn tại'], 404);
            }
    
            return response()->json([
                'data' => BillDetailResource::collection($billDetails),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Chi tiết hóa đơn không tồn tại'], 404);
        }
    }
    
    
}
