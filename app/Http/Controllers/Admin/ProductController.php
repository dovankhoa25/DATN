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

class ProductController extends Controller
{

    public function index(FilterProductRequest $request)
    {
        try {

            $perPage = $request['per_page'] ?? 10;

            $products = Product::with(['productDetails.images', 'category'])
                ->filter($request)
                ->paginate($perPage);

            return ProductResource::collection($products);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sản phẩm rỗng'], 404);
        }
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
                'category_id' => $request->category_id,
            ]);


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
            return new ProductResource($product->load('productDetails.images'));
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
            $product = Product::with('productDetails.images')->findOrFail($id);


            if ($request->hasFile('thumbnail')) {
                if ($product->thumbnail) {
                    Storage::delete($product->thumbnail);
                }

                $thumbnailPath = $this->storeImage($request->file('thumbnail'), 'product/thumbal');
            } else {
                $thumbnailPath = $product->thumbnail;
            }



            $product->update([
                'name' => $request->name,
                'thumbnail' => $thumbnailPath,
                'description' => $request->description,
                'status' => $request->status,
                'category_id' => $request->category_id,
            ]);


            if ($request->has('product_details')) {
                foreach ($request->product_details as $detail) {
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

                    $currentImages = $productDetail->images->pluck('id')->toArray();

                    // $frontendImageIds = array_filter(array_column($detail['images'], 'id'));

                    $frontendImageIds = $detail['image_old'] ?? [];


                    $imagesToDelete = array_diff($currentImages, $frontendImageIds);
                    $imagesToRemove = Image::whereIn('id', $imagesToDelete)->get();

                    foreach ($imagesToRemove as $image) {
                        if (Storage::exists($image->name)) {
                            Storage::delete($image->name);
                        }
                        $image->delete();
                    }
                    // Image::whereIn('id', $imagesToDelete)->delete();


                    if (isset($detail['images']) && is_array($detail['images']) && count($detail['images']) > 0) {
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
            }
            DB::commit();
            return new ProductResource($product->load('productDetails.images'));
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
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
