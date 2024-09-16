<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\FilterCartRequest;
use App\Http\Requests\CartRequest;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(FilterCartRequest $request)
    {
        $objCart = new Cart();
        $perPage = $request['per_page'] ?? 10;

        $query = $objCart->listCart();

        if (!empty($request['name'])) {
            $query->where('pro.name', 'like', '%' . $request['name'] . '%');
        }
        if (!empty($request['sort_by']) && !empty($request['orderby'])) {
            $query->orderBy($request['sort_by'], $request['orderby']);
        }

        $data = $query->paginate($perPage);

        return response()->json([
            'data' => $data,
            'message' => 'success'
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CartRequest $request)
    {

        $productDetail = DB::table('product_details')
            ->select('quantity')
            ->where('id', $request->get('product_detail_id'))
            ->first();

        if ($request->quantity > $productDetail->quantity) {
            return response()->json([
                'error' => 'Số lượng đặt vượt quá số lượng hiện có của sản phẩm.',
                'message' => 'error'
            ], 400);
        }

        $res = Cart::create([
            'ma_bill' => $request->get('ma_bill'),
            'product_detail_id' => $request->get('product_detail_id'),
            'quantity' => $request->get('quantity'),
        ]);
        $cartCollection = new CartResource($res);
        if ($res) {
            // $productDetail->decrement('quantity', $request->get('quantity'));
            DB::table('product_details')
                ->where('id', $request->get('product_detail_id'))
                ->decrement('quantity', $request->get('quantity'));

            return response()->json([
                'data' => $cartCollection,
                'message' => 'success'
            ], 201);
        } else {
            return response()->json(['error' => 'Thêm thất bại']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $ma_bill)
    {
        $objCart = new Cart();
        $data = $objCart->cartByBillCode($ma_bill);

        if ($data) {
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
    public function update(CartRequest $request, string $id)
    {
        $cart = Cart::findOrFail($id);

        $oldProductDetail = DB::table('product_details')
            ->select('quantity')
            ->where('id', $cart->product_detail_id)
            ->first();

        $newProductDetail = DB::table('product_details')
            ->select('quantity')
            ->where('id', $request->get('product_detail_id'))
            ->first();

        if ($request->quantity > $newProductDetail->quantity) {
            return response()->json([
                'error' => 'Số lượng đặt vượt quá số lượng hiện có của sản phẩm mới.',
                'message' => 'error'
            ], 400);
        }

        DB::beginTransaction();
        try {
            if ($cart->product_detail_id != $request->get('product_detail_id')) {

                DB::table('product_details')
                    ->where('id', $cart->product_detail_id)
                    ->increment('quantity', $cart->quantity);

                DB::table('product_details')
                    ->where('id', $request->get('product_detail_id'))
                    ->decrement('quantity', $request->get('quantity'));

                $cart->update([
                    'ma_bill' => $request->get('ma_bill'),
                    'product_detail_id' => $request->get('product_detail_id'),
                    'quantity' => $request->get('quantity'),
                ]);
            } else {
                $quantityDifference = $request->quantity - $cart->quantity;

                if ($quantityDifference != 0) {
                    if ($quantityDifference > 0) {
                        DB::table('product_details')
                            ->where('id', $request->get('product_detail_id'))
                            ->decrement('quantity', $quantityDifference);
                    } else {
                        DB::table('product_details')
                            ->where('id', $request->get('product_detail_id'))
                            ->increment('quantity', -$quantityDifference);
                    }
                    $cart->update([
                        'quantity' => $request->get('quantity'),
                    ]);
                }
            }

            // Commit transaction
            DB::commit();
            $cartCollection = new CartResource($cart);
            return response()->json([
                'data' => $cartCollection,
                'message' => 'Cập nhật giỏ hàng thành công'
            ], 200);
        } catch (\Exception $e) {
            // Rollback transaction nếu có lỗi
            DB::rollBack();
            return response()->json(['error' => 'Cập nhật giỏ hàng thất bại', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cart = Cart::findOrFail($id);

        $res = $cart->delete();
        if ($res) {
            return response()->json(['message' => 'success'], 204);
        } else {
            return response()->json(['error' => 'Xóa thất bại']);
        }
    }
}
