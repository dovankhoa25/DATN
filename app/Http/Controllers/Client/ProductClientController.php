<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\FilterProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductDetail;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductClientController extends Controller
{


    public function getProduct(FilterProductRequest $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $paginatedProducts = Product::filter($request->all())->where('status', 1)
            ->paginate($perPage);

        $formattedProducts = $paginatedProducts->getCollection()->map(function ($product) {
            return Cache::remember('product:' . $product->id, 600, function () use ($product) {
                return Product::getProductWithDetails($product->id);
            });
        });

        $formattedProducts = $formattedProducts->map(function ($product) {
            $minPrice = $product->productDetails->min('price');
            $maxPrice = $product->productDetails->max('price');

            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'categories' => $product->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'image' => $category->image,
                    ];
                }),
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'thumbnail' => $product->thumbnail,
            ];
        });

        if ($formattedProducts->isEmpty()) {
            return response()->json([
                'message' => 'Không có sản phẩm nào',
            ], 404);
        }

        return response()->json([
            'data' => $formattedProducts,
            'current_page' => $paginatedProducts->currentPage(),
            'last_page' => $paginatedProducts->lastPage(),
            'per_page' => $paginatedProducts->perPage(),
            'total' => $paginatedProducts->total(),
        ], 200);
    }



    public function getProductAllWithDetail(FilterProductRequest $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $products = Product::filter($request->all())->where('status', 1)
            ->paginate($perPage);

        $formattedProducts = $products->getCollection()->map(function ($product) {
            return Cache::remember('product:' . $product->id, 600, function () use ($product) {
                return Product::getProductWithDetails($product->id);
            });
        });

        $formattedProducts = $formattedProducts->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'thumbnail' => $product->thumbnail,
                'description' => $product->description,
                'status' => $product->status,
                'categories' => $product->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'image' => $category->image,
                    ];
                }),
                'product_details' => collect($product->productDetails)->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'size' => [
                            'id' => $detail->size->id,
                            'name' => $detail->size->name,
                        ],
                        'price' => $detail->price,
                        'sale' => $detail->sale,
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
            'per_page' => $perPage,
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
        ], 200);
    }



    public function getProductWithDetailByID(int $id)
    {
        $product = Cache::remember('product:' . $id, 600, function () use ($id) {
            return Product::getProductWithDetails($id);
        });

        if ($product) {
            $data = [
                'id' => $product->id,
                'name' => $product->name,
                'thumbnail' => $product->thumbnail,
                'status' => $product->status,
                'description' => $product->description,

                'categories' => $product->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'image' => $category->image,
                    ];
                }),

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

    public function getProductByCate(FilterProductRequest $request, int $id)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $paginatedProducts = Product::filter($request->all())->where('status', 1)
            ->whereHas('categories', function ($query) use ($id) {
                $query->where('categories.id', '=', $id);
            })
            ->paginate($perPage);

        $productIds = $paginatedProducts->pluck('id');

        $products = $productIds->map(function ($id) {
            return Cache::remember('product:' . $id, 600, function () use ($id) {
                return Product::getProductWithDetails($id);
            });
        });

        if ($products->isEmpty()) {
            return response()->json([
                'message' => 'Không có sản phẩm nào',
            ], 404);
        }

        $data = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'thumbnail' => $product->thumbnail,
                'description' => $product->description,
                'categories' => $product->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'image' => $category->image,
                    ];
                }),
                'product_details' => $product->productDetails->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'price' => $detail->price,
                        'sale' => $detail->sale,
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
            'current_page' => $paginatedProducts->currentPage(),
            'last_page' => $paginatedProducts->lastPage(),
            'per_page' => $paginatedProducts->perPage(),
            'total' => $paginatedProducts->total(),
        ], 200);
    }
}
