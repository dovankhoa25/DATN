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
    // public function getProduct(FilterProductRequest $request)
    // {
    //     $perPage = $request->input('per_page', 10);
    //     $products = Product::with(['productDetails'])
    //         ->Filter($request)->paginate($perPage);

    //     if ($products->isEmpty()) {
    //         return response()->json([
    //             'message' => 'Không có sản phẩm nào',
    //         ], 404);
    //     }

    //     $formattedProducts = collect($products->items())->map(function ($product) {
    //         $minPrice = $product->productDetails->min('price');
    //         $maxPrice = $product->productDetails->max('price');

    //         return [
    //             'id' => $product->id,
    //             'name' => $product->name,
    //             'description' => $product->description,
    //             'category' => [
    //                 'id' => $product->category->id,
    //                 'name' => $product->category->name,
    //             ],
    //             'min_price' => $minPrice,
    //             'max_price' => $maxPrice,
    //             'thumbnail' => $product->thumbnail,
    //         ];
    //     });

    //     return response()->json([
    //         'data' => $formattedProducts,
    //         'current_page' => $products->currentPage(),
    //         'last_page' => $products->lastPage(),
    //         'per_page' => $products->perPage(),
    //         'total' => $products->total(),
    //     ], 200);
    // }

    public function getProduct(FilterProductRequest $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $paginatedProducts = Product::filter($request->all())
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

        $formattedProducts = $products->map(function ($product) {
            $minPrice = $product->productDetails->min('price');
            $maxPrice = $product->productDetails->max('price');

            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                // 'category' => [
                //     'id' => $product->category->id,
                //     'name' => $product->category->name,
                // ],
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

        return response()->json([
            'data' => $formattedProducts,
            'current_page' => $paginatedProducts->currentPage(),
            'last_page' => $paginatedProducts->lastPage(),
            'per_page' => $paginatedProducts->perPage(),
            'total' => $paginatedProducts->total(),
        ], 200);
    }

    // cũ
    // public function getProductAllWithDetail(FilterProductRequest $request)
    // {
    //     $perPage = $request->input('per_page', 10);

    //     $products = Product::with(['productDetails.images', 'category', 'productDetails.size'])
    //         ->Filter($request)->paginate($perPage);

    //     $formattedProducts = collect($products->items())->map(function ($product) {
    //         return [
    //             'id' => $product->id,
    //             'name' => $product->name,
    //             'thumbnail' => $product->thumbnail,
    //             'description' => $product->description,
    //             'status' => $product->status,
    //             'category' => [
    //                 'id' => $product->category->id,
    //                 'name' => $product->category->name,
    //                 'image' => $product->category->image
    //             ],
    //             'product_details' => collect($product->productDetails)->map(function ($detail) {
    //                 return [
    //                     'id' => $detail->id,
    //                     'size' => [
    //                         'id' => $detail->size->id,
    //                         'name' => $detail->size->name,
    //                     ],
    //                     'price' => $detail->price,
    //                     'quantity' => $detail->quantity,
    //                     'images' => collect($detail->images)->map(function ($image) {
    //                         return [
    //                             'id' => $image->id,
    //                             'name' => $image->name
    //                         ];
    //                     })
    //                 ];
    //             })
    //         ];
    //     });


    //     return response()->json([
    //         'data' => $formattedProducts,

    //         'total' => $products->total(),
    //         'per_page' => $products->perPage(),
    //         'current_page' => $products->currentPage(),
    //         'last_page' => $products->lastPage(),
    //     ], 200);
    // }


    // mới 
    public function getProductAllWithDetail(FilterProductRequest $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $productIds = Product::filter($request->all())
            ->paginate($perPage)
            ->pluck('id');

        $products = $productIds->map(function ($id) {
            return Cache::remember('product:' . $id, 600, function () use ($id) {
                return Product::getProductWithDetails($id);
            });
        });

        $formattedProducts = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'thumbnail' => $product->thumbnail,
                'description' => $product->description,
                'status' => $product->status,
                // 'category' => [
                //     'id' => $product->category->id,
                //     'name' => $product->category->name,
                //     'image' => $product->category->image
                // ],
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
            'total' => $productIds->count(),
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($productIds->count() / $perPage),
        ], 200);
    }


    public function getProductWithDetailByID(int $id)
    {
        // $product = Product::with(['productDetails.size', 'productDetails.images', 'category'])
        //     ->find($id);
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
                // 'category' => [
                //     'id' => $product->category->id,
                //     'name' => $product->category->name,
                //     'image' => $product->category->image,
                // ],
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




    // public function getProductByCate(FilterProductRequest $request, int $id)
    // {   
    //     $products = Product::with(['productDetails.images'])
    //         ->where('category_id', $id)
    //         ->Filter($request)->get();

    //     if ($products->isEmpty()) {
    //         return response()->json([
    //             'message' => 'Không có sản phẩm nào trong danh mục này',
    //         ], 404);
    //     }


    //     $data = $products->map(function ($product) {
    //         return [
    //             'id' => $product->id,
    //             'name' => $product->name,
    //             'thumbnail' => $product->thumbnail,
    //             'description' => $product->description,
    //             'category' => $product->category->name,
    //             'product_details' => $product->productDetails->map(function ($detail) {
    //                 return [
    //                     'id' => $detail->id,
    //                     'price' => $detail->price,
    //                     'quantity' => $detail->quantity,
    //                     'size' => [
    //                         'id' => $detail->size->id,
    //                         'name' => $detail->size->name,
    //                     ],
    //                     'images' => $detail->images->map(function ($image) {
    //                         return [
    //                             'id' => $image->id,
    //                             'name' => $image->name,
    //                         ];
    //                     }),
    //                 ];
    //             }),
    //         ];
    //     });

    //     return response()->json([
    //         'data' => $data,
    //     ], 200);
    // }


    public function getProductByCate(FilterProductRequest $request, int $id)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $paginatedProducts = Product::filter($request->all())->where('category_id', '=', $id)
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
                // 'category' => [
                //     'id' => $product->category->id,
                //     'name' => $product->category->name,
                // ],
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
