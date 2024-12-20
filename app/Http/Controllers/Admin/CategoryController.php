<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\CategoryRequest;
use App\Http\Requests\Category\FillterCategoryRequest;
use App\Http\Resources\Category\CategoryAdminResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class CategoryController extends Controller
{



    public function index(FillterCategoryRequest $request)
    {
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);

        $cacheKey = 'categories_page_' . $page . '_per_page_' . $perPage;

        // $categories = Cache::remember($cacheKey, 600, function () use ($request, $perPage) {
        //     return Category::with('subcategories')
        //         ->filter($request)
        //         ->whereNull('parent_id')
        //         ->paginate($perPage);
        // });
        $categories = Category::with('subcategories')
            ->filter($request)
            ->whereNull('parent_id')
            ->latest()
            ->paginate($perPage);
        return CategoryAdminResource::collection($categories);
    }



    public function store(CategoryRequest $request)
    {
        $imgUrl = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = date('YmdHi') . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('upload/categories'), $imageName);
            $imgUrl = "/upload/categories/" . $imageName;
        }

        $Category = Category::create([
            'name' => $request->get('name'),
            'image' => $imgUrl,
            'status' => true,
            'parent_id' => $request->get('parent_id') ?? null
        ]);

        $cacheKeys = Redis::keys('categories_page_*');
        foreach ($cacheKeys as $key) {
            Redis::del($key);
        }

        return response()->json([
            'data' => new CategoryAdminResource($Category),
            'message' => 'success'
        ], 201);
    }

    public function show($id)
    {
        try {
            $category = Category::with('subcategories')->findOrFail($id);
            return response()->json([
                'data' => new CategoryAdminResource($category),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'category không tồn tại'], 404);
        }
    }


    // update category or subcategory
    public function update(CategoryRequest $request, string $id)
    {
        try {
            $category = Category::findOrFail($id);
            $imgUrl = $category->image;

            if ($request->hasFile('image')) {
                if ($category->image && file_exists(public_path($category->image))) {
                    unlink(public_path($category->image));
                }
                $image = $request->file('image');
                $imageName = date('YmdHi') . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('upload/categories'), $imageName);
                $imgUrl = "/upload/categories/" . $imageName;
            }

            $category->update([
                'name' => $request->input('name'),
                'image' => $imgUrl ? $imgUrl : $category->image,
                'parent_id' => $request->input('parent_id', $category->parent_id)
            ]);
            // $cacheKeys = Redis::keys('categories_page_*');
            // foreach ($cacheKeys as $key) {
            //     Redis::del($key);
            // }

            return response()->json([
                'data' => new CategoryAdminResource($category),
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
            return CategoryAdminResource::collection($categories);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Không tìm thấy Category'], 404);
        }
    }
}
