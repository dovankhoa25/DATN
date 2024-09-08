<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubcategoryRequest;
use App\Http\Resources\SubcategoryResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
                'per_page' => 'integer|min:1|max:100'
            ]);
            $perPage = $validated['per_page'] ?? 10;
            $data = Category::where('parent_id', '!=', null)->paginate($perPage);
            $categories = SubcategoryResource::collection($data);
            return $categories;
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'SubCategories rỗng'], 404);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SubcategoryRequest $request)
    {
        // Xử lý tệp ảnh
        $imgUrl = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = date('YmdHi') . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('upload/subcategories'), $imageName);
            $imgUrl = "upload/subcategories/" . $imageName;
        }

        $Subcategory = Category::create([
            'name' => $request->get('name'),
            'image' => $imgUrl,
            'status'  => true,
            'parent_id' => $request->get('category_id'),
        ]);

        return response()->json([
            'data' => new SubcategoryResource($Subcategory),
            'parent_id' => $request->get('category_id'),
            'message' => 'success'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $subcategory = Category::where('parent_id', '!=', null)->findOrFail($id);
            return response()->json([
                'data' => new SubcategoryResource($subcategory),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'subcategory không tồn tại'], 404);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(SubcategoryRequest $request, string $id)
    {
        try {
            $imgUrl = null;
            $subcategory = Category::where('parent_id', '!=', null)->findOrFail($id);
            if ($request->hasFile('image')) {
                // delete image old
                if ($subcategory->image != null) {
                    unlink(public_path($subcategory->image));
                }

                $image = $request->file('image');
                $imageName = date('YmdHi') . $image->getClientOriginalName();
                $image->move(public_path('upload/subcategories'), $imageName);
                $imgUrl = "upload/subcategories/" . $imageName;
            }
            $subcategory->update([
                'name' => $request->get('name'),
                'image' => $imgUrl,
                'parent_id' => $request->get('category_id'),
            ]);
            return response()->json([
                'data' => new SubcategoryResource($subcategory),
                'message' => 'success',
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'subcategory không tồn tại'], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $subcategory = Category::where('parent_id', '!=', null)->findOrFail($id);
            $subcategory->delete();
            return response()->json([
                'message' => 'xoá subcategory thành công'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Subcategory không tồn tại'], 404);
        }
    }

    // Lấy subcategories gốc bên client
    public function getSubCategoriesRoot()
    {
        try {
            $subCategories = Category::whereNotNull('parent_id')->where('status', true)->get();
            return response()->json([
                'data' => $subCategories,
                'message' => 'success'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'SubCategories rỗng'], 404);
        }
    }

    // lấy all products dựa trên 1 subcategory cụ thể
    public function getProducts($subCate_id)
    {
        try {
            $Products = Product::where('category_id', $subCate_id)->where('status', true)->get();
            if ($Products->isEmpty()) {
                return response()->json(['error' => 'Không có sản phẩm nào cho subcategory này'], 404);
            }
            return response()->json([
                'data' => $Products,
                'message' => 'success'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'SubCategories này Không có products nào !'], 404);
        }
    }

    // lấy all subcategories cùng all product
    public function getAllSubcateAndAllProducts()
    {
        try {
            $subcategories = Category::with('products')
                ->whereNotNull('parent_id')
                ->where('status', true)
                ->get();

            return response()->json(['data' => $subcategories], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Category này Không có subcategory nào !'], 404);
        }
    }
}
