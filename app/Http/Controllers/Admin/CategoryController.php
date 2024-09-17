<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\CategoryRequest;
use App\Http\Requests\Category\FillterCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CategoryController extends Controller
{

    public function index(FillterCategoryRequest $request)
    {
        try {
            $perPage = $request->get('per_page', 10);

            $categories = Category::with('subcategories')
                ->filter($request)
                ->whereNull('parent_id')
                ->paginate($perPage);
            return CategoryResource::collection($categories);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Không tìm thấy Category'], 404);
        }
    }

    public function store(CategoryRequest $request)
    {
        $imgUrl = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = date('YmdHi') . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('upload/categories'), $imageName);
            $imgUrl = "upload/categories/" . $imageName;
        }

        $Category = Category::create([
            'name' => $request->get('name'),
            'image' => $imgUrl,
            'status' => true,
            'parent_id' => $request->get('parent_id') ?? Null
        ]);

        return response()->json([
            'data' => new CategoryResource($Category),
            'message' => 'success'
        ], 201);
    }

    public function show($id)
    {
        try {
            $category = Category::with('subcategories')->findOrFail($id);
            return response()->json([
                'data' => new CategoryResource($category),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'category không tồn tại'], 404);
        }
    }


    // update category or subcategory
    public function update(CategoryRequest $request, string $id)
    {
        try {
            $imgUrl = null;
            $category = Category::findOrFail($id);
            if ($request->hasFile('image')) {
                // delete image old
                if ($category->image != null) {
                    unlink(public_path($category->image));
                }

                $image = $request->file('image');
                $imageName = date('YmdHi') . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('upload/categories'), $imageName);
                $imgUrl = "upload/categories/" . $imageName;
            }

            $category->update([
                'name' => $request->input('name'),
                'image' => $imgUrl ? $imgUrl : $category->image,
                'parent_id' => $request->input('parent_id') ?? Null
            ]);
            return response()->json([
                'data' => new CategoryResource($category),
                'message' => 'success',
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'category không tồn tại'], 404);
        }
    }

    // xoá category
    public function destroy(string $id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->delete();
            return response()->json([
                'message' => 'xoá category thành công'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'category không tồn tại'], 404);
        }
    }

    // update status
    public function updateStatus(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|boolean'
            ]);
            $category = Category::findOrFail($id);

            $category->update(['status' => $validated['status']]);

            if (!$validated['status']) {
                $category->subcategories()->update(['status' => false]);
            }

            return response()->json([
                'message' => 'Cập nhật status Category thành công !'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'update status thất bại'], 404);
        }
    }

    // list categories
    public function listCategories(FillterCategoryRequest $request)
    {

        try {
            $perPage = $request->get('per_page', 10);

            $categories = Category::filter($request)->paginate($perPage);
            return CategoryResource::collection($categories);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Không tìm thấy Category'], 404);
        }
    }

    // // Lấy category gốc bên client
    // public function getCategoriesRoot()
    // {
    //     try {
    //         $categories = Category::whereNull('parent_id')->where('status', true)->get();
    //         return response()->json([
    //             'data' => $categories,
    //             'message' => 'success'
    //         ], 200);
    //     } catch (ModelNotFoundException $e) {
    //         return response()->json(['error' => 'Categories rỗng'], 404);
    //     }
    // }

    // // lấy all subcategories dựa trên 1 category cụ thể
    // public function getSubcategories($id)
    // {
    //     try {
    //         $subCategories = Category::where('parent_id', $id)->where('status', true)->get();
    //         return response()->json([
    //             'data' => $subCategories,
    //             'message' => 'success'
    //         ], 200);
    //     } catch (ModelNotFoundException $e) {
    //         return response()->json(['error' => 'Category này Không có subcategory nào !'], 404);
    //     }
    // }

    // // lấy all categories cùng all subcategories
    // public function getAllCateAndAllSubcate()
    // {
    //     try {
    //         $data = Category::with('subcategories')->whereNull('parent_id')->where('status', true)->get();
    //         return response()->json([
    //             'data' => $data,
    //             'message' => 'success'
    //         ], 200);
    //     } catch (ModelNotFoundException $e) {
    //         return response()->json(['error' => 'Category này Không có subcategory nào !'], 404);
    //     }
    // }
}
