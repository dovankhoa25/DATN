<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CategoryController extends Controller
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
            $data = Category::paginate($perPage);
            $categories = CategoryResource::collection($data);
            return $categories;
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Categories rỗng'], 404);
        }
    }

    /**
     * Show the form for creating a new resource.
     */

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        // Xử lý tệp ảnh
        $imgUrl = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = date('YmdHi') . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('upload/categories'), $imageName);
            $imgUrl = "upload/categories/" . $imageName;
        }

        $Category = Category::create([
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'image' => $imgUrl,
        ]);

        return response()->json([
            'data' => new CategoryResource($Category),
            'message' => 'success'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $category = Category::findOrFail($id);
            return response()->json([
                'category' => new CategoryResource($category),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'category không tồn tại'], 404);
        }
    }


    /**
     * Update the specified resource in storage.
     */
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
                'name' => $request->get('name'),
                'description' => $request->get('description'),
                'image' => $imgUrl,
            ]);
            return response()->json([
                'data' => new CategoryResource($category),
                'message' => 'success',
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'category không tồn tại'], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->delete(); // Xóa mềm
            return response()->json([
                'message' => 'xoá category thành công'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'category không tồn tại'], 404);
        }
    }
}
