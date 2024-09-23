<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CategoryController extends Controller
{
    // all categories -> subcategories -> products
    public function index()
    {
        try {
            $categories = Category::with([
                'subcategories' => function ($query) {
                    $query->where('status', true);
                }
            ])
                ->where('status', true)
                ->whereNull('parent_id')
                ->get();

            return CategoryResource::collection($categories);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Categories rỗng !'
            ]);
        }
    }


    public function show(string $id)
    {
        try {
            $category = Category::with([
                'subcategories' => function ($query) {
                    $query->where('status', true);
                }
            ])
                ->whereNull('parent_id')
                ->findOrFail($id);
            return new CategoryResource($category);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Không tìm thấy Category !'
            ]);
        }
    }
}
