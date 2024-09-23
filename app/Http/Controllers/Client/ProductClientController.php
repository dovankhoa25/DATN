<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\FilterProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ProductClientController extends Controller
{
    public function getProduct(Request $request){

            $perPage = $request['per_page'] ?? 10;

            $products = Product::paginate($perPage);

            return response()->json([
                'data' => $products,
            ],201);
       
    }
    


    public function getProductAllWithDetail(Request $request){

            $perPage = $request['per_page'] ?? 10;

            $products = $products = Product::with(['productDetails.images.sizes'])->paginate($perPage);

            return response()->json([
                'data' => $products,
            ],201);

    }



    public function getProductWithDetailByID(int $id){

        $products = Product::with(['productDetails.images.sizes.category'])
        ->where('id' ,'=',$id)
        ->first();

        return response()->json([
            'data' => $products,
        ],201);

    }


    public function getProductCate(int $id){

        $products = Product::with(['productDetails.images'])
        ->where('category_id' ,'=',$id)
        ->get();
        
        return response()->json([
            'data' => $products,
        ],201);
    }

    

}
