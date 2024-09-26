<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\FilterProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductDetail;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ProductClientController extends Controller
{
    public function getProduct(FilterProductRequest $request)
    {
        $perPage = $request->input('per_page', 10);
        $products = Product::with(['productDetails'])
            ->Filter($request)->paginate($perPage);

        if ($products->isEmpty()) {
            return response()->json([
                'message' => 'Không có sản phẩm nào',
            ], 404);
        }

        $formattedProducts = collect($products->items())->map(function ($product) {
            $minPrice = $product->productDetails->min('price');
            $maxPrice = $product->productDetails->max('price');

            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'category' => [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                ],
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'thumbnail' => $product->thumbnail,
            ];
        });

        return response()->json([
            'data' => $formattedProducts,
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(),
            'total' => $products->total(),
        ], 200);
    }




    public function getProductAllWithDetail(FilterProductRequest $request)
    {
        $perPage = $request->input('per_page', 10);

        $products = Product::with(['productDetails.images', 'category', 'productDetails.size'])
            ->Filter($request)->paginate($perPage);

        $formattedProducts = collect($products->items())->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'thumbnail' => $product->thumbnail,
                'description' => $product->description,
                'status' => $product->status,
                'category' => [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'image' => $product->category->image
                ],
                'product_details' => collect($product->productDetails)->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'size' => [
                            'id' => $detail->size->id,
                            'name' => $detail->size->name,
                        ],
                        'price' => $detail->price,
                        'quantity' => $detail->quantity,
                        'images' => collect($detail->images)->map(function ($image) {
                            return [
                                'id' => $image->id,
                                'name' => $image->name
                            ];
                        })
                    ];
                })
            ];
        });


        return response()->json([
            'data' => $formattedProducts,

            'total' => $products->total(),
            'per_page' => $products->perPage(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
        ], 200);
    }




    public function getProductWithDetailByID(int $id)
    {
        $product = Product::with(['productDetails.size', 'productDetails.images', 'category'])
            ->find($id);

        if ($product) {
            $data = [
                'id' => $product->id,
                'name' => $product->name,
                'thumbnail' => $product->thumbnail,
                'status' => $product->status,
                'description' => $product->description,
                'category' => [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'image' => $product->category->image,
                ],
                'product_details' => $product->productDetails->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'price' => $detail->price,
                        'quantity' => $detail->quantity,
                        'sale' => $detail->sale,
                        'size' => [
                            'id' => $detail->size->id,
                            'name' => $detail->size->name,
                        ],
                        'images' => $detail->images->map(function ($image) {
                            return [
                                'id' => $image->id,
                                'name' => $image->name,
                            ];
                        }),
                    ];
                }),
            ];

            return response()->json([
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Không tìm thấy sản phẩm với ID này',
            ], 404);
        }
    }




    public function getProductByCate(FilterProductRequest $request,int $id)
    {
        $products = Product::with(['productDetails.images'])
            ->where('category_id', $id)
            ->Filter($request)->get();

        if ($products->isEmpty()) {
            return response()->json([
                'message' => 'Không có sản phẩm nào trong danh mục này',
            ], 404);
        }


        $data = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'thumbnail' => $product->thumbnail,
                'description' => $product->description,
                'category' => $product->category->name,
                'product_details' => $product->productDetails->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'price' => $detail->price,
                        'quantity' => $detail->quantity,
                        'size' => [
                            'id' => $detail->size->id,
                            'name' => $detail->size->name,
                        ],
                        'images' => $detail->images->map(function ($image) {
                            return [
                                'id' => $image->id,
                                'name' => $image->name,
                            ];
                        }),
                    ];
                }),
            ];
        });

        return response()->json([
            'data' => $data,
        ], 200);
    }
}
