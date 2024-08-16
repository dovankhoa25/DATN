<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubcategoryRequest;
use App\Http\Resources\SubcategoryResource;
use App\Models\Subcategory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $data = Subcategory::all();
        $subcategories = SubcategoryResource::collection($data);
        if ($subcategories->count() > 0) {
            return response()->json([
                'data' => $subcategories,
                'message' => 'successs'
            ], 200);
        } else {
            return response()->json([
                'message' => 'Không có Subcategory nào !'
            ], 200);
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
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = date('YmdHi') . $image->getClientOriginalName();
            $image->move(public_path('upload/subcategories'), $imageName);

            $Category = Subcategory::create([
                'name' => $request->get('name'),
                'description' => $request->get('description'),
                'image' => $imageName,
                'categorie_id' => $request->get('categorie_id'),
            ]);
        } else {
            $Category = Subcategory::create([
                'name' => $request->get('name'),
                'description' => $request->get('description'),
                'categorie_id' => $request->get('categorie_id'),
            ]);
        }
        return response()->json([
            'data' => new SubcategoryResource($Category),
            'message' => 'success'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $subcategory = Subcategory::findOrFail($id);
            return response()->json([
                'subcategory' => new SubcategoryResource($subcategory),
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
            $subcategory = Subcategory::findOrFail($id);
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = date('YmdHi') . $image->getClientOriginalName();
                $image->move(public_path('upload/subcategories'), $imageName);

                $subcategory->update([
                    'name' => $request->get('name'),
                    'description' => $request->get('description'),
                    'image' => $imageName,
                    'categorie_id' => $request->get('categorie_id'),

                ]);
            } else {
                $subcategory->update([
                    'name' => $request->get('name'),
                    'description' => $request->get('description'),
                    'categorie_id' => $request->get('categorie_id'),
                ]);
            }
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
            $subcategory = Subcategory::findOrFail($id);
            $subcategory->delete(); // Xóa mềm
            return response()->json([
                'message' => 'xoá subcategory thành công'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Subcategory không tồn tại'], 404);
        }
    }
}
