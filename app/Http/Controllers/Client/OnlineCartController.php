<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\OnlCart\OnlCartRequest;
use App\Http\Resources\OnlCartResource;
use App\Models\OnlineCart;
use App\Models\ProductDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class OnlineCartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $objOnlCart = new OnlineCart();

            $cartItems = $objOnlCart->onlCartByUserId($user->id)->get();

            $itemsToRemove = [];
            foreach ($cartItems as $item) {
                $productDetail = DB::table('product_details')
                    ->where('id', $item->product_detail_id)
                    ->first();

                if ($productDetail && $productDetail->quantity < $item->quantity) {
                    $itemsToRemove[] = $item->id;
                }
            }

            if (!empty($itemsToRemove)) {
                DB::table('online_cart')
                    ->whereIn('id', $itemsToRemove)
                    ->delete();
            }

            $data = $objOnlCart->onlCartByUserId($user->id)->get();

            if ($data) {
                return response()->json([
                    'data' => $data,
                    'message' => 'success'
                ], 200);
            } else {
                return response()->json(['message' => 'Giỏ hàng trống'], 404);
            }

        } catch (JWTException $e) {
            return response()->json(['message' => $e], 403);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OnlCartRequest $request)
    {
        $productDetail = DB::table('product_details')
            ->select('quantity', 'price', 'sale')
            ->where('id', $request->get('product_detail_id'))
            ->first();

        $price = $productDetail->sale ?? $productDetail->price;

        $user = JWTAuth::parseToken()->authenticate();
        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại'], 404);
        }


        $quantityProduct = $productDetail->quantity;
        if ($request->quantity > $quantityProduct) {
            return response()->json([
                'error' => 'Số lượng đặt vượt quá số lượng hiện có của sản phẩm.',
                'message' => 'error',
                'data' => $quantityProduct,
            ], 400);
        }

        $existingCart = OnlineCart::where('user_id', $user->id)
            ->where('product_detail_id', $request->get('product_detail_id'))
            ->first();

        if ($existingCart) {
            $newQuantity = $existingCart->quantity + $request->get('quantity');
            if ($newQuantity > $productDetail->quantity) {
                return response()->json([
                    'error' => 'Số lượng đặt vượt quá số lượng hiện có của sản phẩm.',
                    'message' => 'error',
                    'data' => $quantityProduct,
                ], 400);
            }

            $existingCart->update([
                'quantity' => $newQuantity,
                'price' => $price * $newQuantity,
            ]);

            $onlCartCollection = new OnlCartResource($existingCart);
            return response()->json([
                'data' => $onlCartCollection,
                'message' => 'success'
            ], 200);
        } else {
            $productDetail = ProductDetail::find($request->get('product_detail_id'));

            if (!$productDetail) {
                return response()->json(['error' => 'Sản phẩm không tồn tại.'], 404);
            }

            $res = OnlineCart::create([
                'user_id' => $user->id,
                'product_detail_id' => $productDetail->id,
                'quantity' => $request->get('quantity'),
                'price' => $productDetail->sale ? $productDetail->sale : $productDetail->price  * $request->get('quantity'),
            ]);

            if ($res) {
                return response()->json([
                    'data' => [
                        'product_name' => $productDetail->product->name,
                        'price' => $productDetail->price,
                        'quantity' => $res->quantity,
                        'total_price' => $res->price,
                    ],
                    'message' => 'success'
                ], 201);
            } else {
                return response()->json(['error' => 'Thêm thất bại'], 500);
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $idUser)
    {
        $objOnlCart = new OnlineCart();
        $validated = $request->validate([
            'per_page' => 'integer|min:1|max:100'
        ]);
        $perPage = $validated['per_page'] ?? 10;

        $cartItems = $objOnlCart->onlCartByUserId($idUser)->get();

        $itemsToRemove = [];
        foreach ($cartItems as $item) {
            $productDetail = DB::table('product_details')
                ->where('id', $item->product_detail_id)
                ->first();

            if ($productDetail && $productDetail->quantity < $item->quantity) {
                $itemsToRemove[] = $item->id;
            }
        }

        if (!empty($itemsToRemove)) {
            DB::table('online_cart')
                ->whereIn('id', $itemsToRemove)
                ->delete();
        }

        $data = $objOnlCart->onlCartByUserId($idUser)->paginate($perPage);

        if ($data->total() > 0) {
            return response()->json([
                'data' => $data,
                'message' => 'success'
            ], 200);
        } else {
            return response()->json(['message' => 'Giỏ hàng trống'], 404);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $cartItem = DB::table('online_cart')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Không tìm thấy thông tin giỏ hàng cần sửa'], 404);
        }

        $productDetail = DB::table('product_details')
            ->select('quantity', 'price', 'sale')
            ->where('id', $cartItem->product_detail_id)
            ->first();

        $price = $productDetail->sale ?? $productDetail->price;

        if ($request->quantity > $productDetail->quantity) {
            return response()->json([
                'error' => 'Số lượng đặt vượt quá số lượng hiện có của sản phẩm.',
                'message' => 'error',
                'data' => [
                    'soluong' => $productDetail->quantity,
                    'soluongkhachguilen' => $request->quantity,
                ],
            ], 400);
        }

        $validated = $request->validate([
            'quantity' => 'integer|min:1|max:100'
        ]);
        $quantity = $validated['quantity'];

        // Cập nhật giỏ hàng
        DB::table('online_cart')
            ->where('id', $id)
            ->update([
                'quantity' => $quantity,
                'price' => $price * $request->get('quantity'),
                'updated_at' => now()
            ]);
        $updatedCartItem = DB::table('online_cart')->where('id', $id)->first();

        $onlCartCollection = new OnlCartResource($updatedCartItem);
        return response()->json([
            'data' => $onlCartCollection,
            'message' => 'success'
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cart = OnlineCart::findOrFail($id);

        $res = $cart->delete();
        if ($res) {
            return response()->json(['message' => 'success'], 204);
        } else {
            return response()->json(['error' => 'Xóa thất bại']);
        }
    }
}
