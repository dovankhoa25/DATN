<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartRequest;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $objCart = new Cart();
        $data = $objCart->listCart();
        return response()->json([
            'data' => $data,
            'message' => 'success'], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CartRequest $request)
    {
        $data = $request->all();
        $res = Cart::create($data);
        $cartCollection = new CartResource($res);
        if($res){
            return response()->json([
                'data' => $cartCollection,
                'message' => 'success'], 201);
        }else{
            return response()->json(['error'=>'Thêm thất bại']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $checkId = Cart::findOrFail($id);
        if($checkId){
            $objCart = new Cart();
            $data = $objCart->cartById($id);
            return response()->json([
                'data' => $data,
                'message' => 'success'], 200);
        }else{
            return response()->json(['message'=>'id không tồn tại'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CartRequest $request, string $id)
    {
        $data = $request->all();
        $cart = Cart::findOrFail($id);
        $res = $cart->update($data);
        $cartCollection = new CartResource($cart);
        if($res){
            return response()->json([
                'data' => $cartCollection,
                'message' => 'success'], 200);
        }else{
            return response()->json(['error'=>'Thêm thất bại']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cart = Cart::findOrFail($id);

        $res = $cart->delete();
        if($res){
            return response()->json(['message'=>'success'], 204);
        }else{
            return response()->json(['error'=>'Xóa thất bại']);
        }
    }
}
