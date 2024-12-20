<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\FilterProductRequest;
use App\Http\Requests\Product\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Image;
use App\Models\Product;
use App\Models\ProductDetail;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{

    // public function index(FilterProductRequest $request)
    // {
    //     try {

    //         $perPage = $request['per_page'] ?? 10;

    //         $products = Product::with(['productDetails.images', 'category'])
    //             ->filter($request)
    //             ->paginate($perPage);

    //         return ProductResource::collection($products);
    //     } catch (ModelNotFoundException $e) {
    //         return response()->json(['error' => 'Sản phẩm rỗng'], 404);
    //     }
    // }


    public function index(FilterProductRequest $request)
    {
        $perPage = $request->get('per_page', 10);

        $paginatedProductIds = Product::filter($request->all())
            ->latest()
            ->select('id')
            ->paginate($perPage);

        $productIds = $paginatedProductIds->pluck('id');

        $products = $productIds->map(function ($id) {
            return Cache::remember('product:' . $id, 600, function () use ($id) {
                return Product::getProductWithDetails($id);
            });
        });

        $paginatedProductIds->setCollection($products);

        return  ProductResource::collection($paginatedProductIds);
    }



    protected function storeImage($file, $directory)
    {
        if ($file) {
            $filePath = $file->store($directory, 'public');
            return Storage::url($filePath); // Trả về URL công khai
        }

        return null;
    }

    public function store(ProductRequest $request)
    {
        DB::beginTransaction();


        try {
            $product = Product::create([
                'name' => $request->name,
                'thumbnail' => $this->storeImage($request->file('thumbnail'), 'product/thumbal'),
                'description' => $request->description,
                'status' => true,
                // 'category_id' => $request->category_id,
            ]);


            if ($request->categories && is_array($request->categories)) {
                $product->categories()->attach($request->categories);
            }

            foreach ($request->product_details as $detail) {
                $productDetail = ProductDetail::create([
                    'size_id' => $detail['size_id'],
                    'price' => $detail['price'],
                    'quantity' => $detail['quantity'],
                    'sale' => $detail['sale'],
                    'status' => true,
                    'product_id' => $product->id,
                ]);

                foreach ($detail['images'] as $img) {
                    Image::create([
                        'name' => $this->storeImage($img['file'], 'product/images'),
                        'product_detail_id' => $productDetail->id,
                    ]);
                }
            }

            DB::commit();
            // $product->load('productDetails.images', 'category');
            $product = Product::getProductWithDetails($product->id);
            Cache::put('product:' . $product->id, $product, 600);

            return new ProductResource($product);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }


    public function show($id)
    {
        $product = Product::with('productDetails.images')->findOrFail($id);

        return new ProductResource($product);
    }



    public function update(ProductRequest $request, $id)
    {
        DB::beginTransaction();

        try {

            $sizeIds = array_column($request->product_details, 'size_id');
            if (count($sizeIds) !== count(array_unique($sizeIds))) {
                return response()->json(['message' => 'không thể thêm trùng size'], 422);
            }
            $product = Product::with('productDetails.images')->findOrFail($id);

            $thumbnailPath = $this->handleThumbnail($request, $product);

            $product->update([
                'name' => $request->name,
                'thumbnail' => $thumbnailPath,
                'description' => $request->description,
                'status' => $request->status,
                // 'category_id' => $request->category_id,
            ]);

            // Cập nhật danh mục
            if ($request->categories && is_array($request->categories)) {
                $product->categories()->sync($request->categories);
            }


            if ($request->has('product_details')) {
                $this->syncProductDetails($request->product_details, $product);
            }

            DB::commit();

            $product = Product::getProductWithDetails($product->id);
            Cache::put('product:' . $product->id, $product, 600);


            return new ProductResource($product);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }



    private function handleThumbnail($request, $product)
    {
        if ($request->hasFile('thumbnail')) {
            if ($product->thumbnail) {
                Storage::delete($product->thumbnail);
            }

            return $this->storeImage($request->file('thumbnail'), 'product/thumbal');
        }

        return $product->thumbnail;
    }

    private function syncProductDetails($details, $product)
    {
        foreach ($details as $detail) {
            $productDetail = ProductDetail::updateOrCreate(
                ['id' => $detail['id'] ?? null],
                [
                    'size_id' => $detail['size_id'],
                    'price' => $detail['price'],
                    'quantity' => $detail['quantity'],
                    'sale' => $detail['sale'],
                    'status' => $detail['status'] ?? 1,
                    'product_id' => $product->id,
                ]
            );

            $this->syncProductImages($detail, $productDetail);
        }
    }

    private function syncProductImages($detail, $productDetail)
    {
        $currentImages = $productDetail->images->pluck('id')->toArray();
        $frontendImageIds = $detail['image_old'] ?? [];

        $imagesToDelete = array_diff($currentImages, $frontendImageIds);
        $imagesToRemove = Image::whereIn('id', $imagesToDelete)->get();

        foreach ($imagesToRemove as $image) {
            if (Storage::exists($image->name)) {
                Storage::delete($image->name);
            }
            $image->delete();
        }

        if (isset($detail['images']) && is_array($detail['images'])) {
            foreach ($detail['images'] as $img) {
                if (isset($img['id'])) {
                    Image::where('id', $img['id'])->update([
                        'name' => $img['file'] ? $this->storeImage($img['file'], 'product/images') : null,
                        'status' => $img['status'] ?? true,
                    ]);
                } else {
                    Image::create([
                        'name' => $this->storeImage($img['file'], 'product/images'),
                        'product_detail_id' => $productDetail->id,
                    ]);
                }
            }
        }
    }




    public function destroy(Product $Product)
    {
        $Product = Product::findOrFail($Product);
        $Product->delete();
        return response()->json(null, 204);
    }




    public function updateStatus(Request $request, string $id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->status = !$product->status;
            $product->save();

            // Cache::put('product:' . $product->id, $product->load('productDetails.images'), 600);

            Cache::put('product:' . $product->id, Product::getProductWithDetails($product->id), 600);
            if ($product->status) {
                return response()->json(['message' => 'hiện'], 200);
            } else {
                return response()->json(['message' => 'ẩn'], 200);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'sản phẩm không tồn tại'], 404);
        }
    }
}
