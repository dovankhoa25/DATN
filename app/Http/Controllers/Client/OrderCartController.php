<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderCart\OrderCartRequest;
use App\Models\OrderCart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\NewOrderPlaced;

class OrderCartController extends Controller
{


    /**
     * Store a newly created resource in storage.
     */
    public function store(OrderCartRequest $request)
    {
        $bill = DB::table('bills')
            ->select('status')
            ->where('ma_bill', $request->get('ma_bill'))
            ->first();

        if ($bill->status !== 'pending') {
            return response()->json([
                'message' => 'Mã bill này đã hoàn thành xử lí, không thể thêm',
            ], 400);
        }

        $productDetail = DB::table('product_details')
            ->select('quantity', 'price', 'sale')
            ->where('id', $request->get('product_detail_id'))
            ->first();

        $price = $productDetail->sale ?? $productDetail->price;


        $existingCartItem = OrderCart::where('ma_bill', $request->get('ma_bill'))
            ->where('product_detail_id', $request->get('product_detail_id'))
            ->first();

        if ($existingCartItem) {
            $newQuantity = $existingCartItem->quantity + $request->get('quantity');

            if ($newQuantity > $productDetail->quantity) {
                return response()->json([
                    'message' => 'Số lượng đặt vượt quá số lượng hiện có của sản phẩm. Số lượng sản phẩm hiện tại : ' . $productDetail->quantity,
                    'quantity' =>  $productDetail->quantity,
                ], 400);
            }

            $existingCartItem->update(['quantity' => $newQuantity]);

            $data = $existingCartItem->makeHidden(['created_at', 'updated_at']);
            return response()->json([
                'data' => $data,
                'message' => 'Số lượng sản phẩm đã được cập nhật thành công',
            ], 200);
        } else {

            $res = OrderCart::create([
                'ma_bill' => $request->get('ma_bill'),
                'product_detail_id' => $request->get('product_detail_id'),
                'quantity' => $request->get('quantity'),
                'price' => $price,
            ]);

            if ($res) {
                $data = $res->makeHidden(['created_at', 'updated_at']);
                return response()->json([
                    'data' => $data,
                    'message' => 'Thêm sản phẩm vào giỏ hàng thành công',
                ], 201);
            } else {
                return response()->json(['error' => 'Thêm thất bại'], 400);
            }
        }
    }

    public function show(Request $request, string $ma_bill)
    {
        $bill = DB::table('bills')
            ->select('status')
            ->where('ma_bill', $ma_bill)
            ->first();

        if (!$bill) {
            return response()->json(['message' => 'Không tìm thấy thông tin bill'], 404);
        }

        if ($bill->status !== 'pending') {
            return response()->json([
                'message' => 'Mã bill này đã xử lí xong, không thể xem :)',
            ], 400);
        }

        $objCart = new OrderCart();
        $validated = $request->validate([
            'per_page' => 'integer|min:1|max:100'
        ]);
        $perPage = $validated['per_page'] ?? 10;

        $data = $objCart->listCart()->where('ma_bill', $ma_bill)->paginate($perPage);

        if ($data->total() > 0) {
            return response()->json([
                'data' => $data,
                'message' => 'success'
            ], 200);
        } else {
            return response()->json(['message' => 'Mã bill không tồn tại'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OrderCartRequest $request)
    {
        $cartItem = DB::table('oder_cart')->where('id', $request->id_cart_order)->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Không tìm thấy thông tin giỏ hàng cần sửa'], 404);
        }

        $bill = DB::table('bills')
            ->select('status')
            ->where('ma_bill', $cartItem->ma_bill)
            ->first();

        if (!$bill) {
            return response()->json(['message' => 'Không tìm thấy thông tin bill'], 404);
        }

        if ($bill->status !== 'pending') {
            return response()->json([
                'error' => 'Mã bill này đã xử lí xong, không thể sửa :)',
                'message' => 'error'
            ], 400);
        }

        $productDetail = DB::table('product_details')
            ->select('quantity')
            ->where('id', $cartItem->product_detail_id)
            ->first();

        if ($request->quantity > $productDetail->quantity) {
            return response()->json([
                'message' => 'Số lượng đặt vượt quá số lượng hiện có của sản phẩm.số lượng hiện có của sản phẩm là ' . $productDetail->quantity,
            ], 400);
        }

        $res = DB::table('oder_cart')
            ->where('id', $request->id_cart_order)
            ->update([
                'quantity' => $request->quantity
            ]);

        if ($res !== false) {
            $updatedCartItem = DB::table('oder_cart')->where('id', $request->id_cart_order)->first();
            $data = collect($updatedCartItem)->except(['created_at', 'updated_at'])->toArray();
            return response()->json([
                'data' => $data,
                'message' => 'success'
            ], 200);
        } else {
            return response()->json(['message' => 'Cập nhật thất bại'], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $cart = OrderCart::findOrFail($id);

        $res = $cart->delete();
        if ($res) {
            return response()->json(['message' => 'success'], 204);
        } else {
            return response()->json(['error' => 'Xóa thất bại']);
        }
    }
}
