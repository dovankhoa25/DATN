<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Image;
use App\Models\Product;
use App\Models\ProductDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class ProductController extends Controller
{

    public function index()
    {
        $products = Product::with(['productDetails.images'])->paginate(20);

        return ProductResource::collection($products);
    }


    public function create()
    {
        //
    }


    protected function storeImage($file, $directory)
    {
        if ($file) {
            return $file->store($directory, 'public');
        }

        return null;
    }

    public function store(ProductRequest $request)
    {
        $product = Product::create([
            'name' => $request->name,
            'thumbnail' => $this->storeImage($request->file('thumbnail'), 'product/thumbal'),
            'status' => $request->status,
            'sub_categories_id' => $request->sub_categories_id,
        ]);

        foreach ($request->product_details as $detail) {
            $productDetail = ProductDetail::create([
                'size_id' => $detail['size_id'],
                'price' => $detail['price'],
                'quantity' => $detail['quantity'],
                'sale' => $detail['sale'],
                'status' => $detail['status'],
                'product_id' => $product->id,
            ]);

            foreach ($detail['images'] as $img) {
                Image::create([
                    'name' => $this->storeImage($img['file'], 'product/images'),
                    'status' => $img['status'],
                    'product_detail_id' => $productDetail->id,
                ]);
            }
        }

        return new ProductResource($product->load('productDetails.images'));
    }


    public function show($id)
    {
        $product = Product::with('productDetails.images')->findOrFail($id);

        return new ProductResource($product);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        //
    }


    public function update(ProductRequest $request, $id)
    {
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
            'status' => $request->status,
            'sub_categories_id' => $request->sub_categories_id,
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
                        'status' => $detail['status'],
                        'product_id' => $product->id,
                    ]
                );

                $currentImages = $productDetail->images->pluck('id')->toArray();
                $frontendImageIds = array_filter(array_column($detail['images'], 'id'));
                $imagesToDelete = array_diff($currentImages, $frontendImageIds);

                Image::whereIn('id', $imagesToDelete)->delete();

                foreach ($detail['images'] as $img) {
                    if (isset($img['id'])) {
                        Image::where('id', $img['id'])->update([
                            'status' => $img['status']
                        ]);
                    } else {
                        Image::create([
                            'name' => $this->storeImage($img['file'], 'product/images'),
                            'status' => $img['status'],
                            'product_detail_id' => $productDetail->id,
                        ]);
                    }
                }


            }
        }

        return new ProductResource($product->load('productDetails.images'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $Product)
    {
        //
    }
}
