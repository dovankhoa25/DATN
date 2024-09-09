<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SizeRequest;
use App\Http\Resources\SizeResource;
use App\Models\Size;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'per_page' => 'integer|min:1|max:100'
        ]);
        $perPage = $validated['per_page'] ?? 10;
        $listSize = Size::paginate($perPage);
        // $sizeCollection = SizeResource::collection($listSize);
        return SizeResource::collection($listSize,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SizeRequest $request)
    {
        $sizedata = $request->all();
        $sizedata['status'] = true;
       
        $size = Size::create($sizedata);

        $sizeCollection = new SizeResource($size);

        if ($size) {
            return response()->json($sizeCollection, 201);
        } else {
            return response()->json(['error', 'Thêm size thất bại']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $size = Size::FindorFail($id);
        $sizeCollection = new SizeResource($size);
        if ($size) {
            return response()->json($sizeCollection, 200);
        } else {
            return response()->json(['error', 'Không tìm thấy size theo id']);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SizeRequest $request, string $id)
    {
        $size = Size::FindorFail($id);
        $sizeData = $request->all();

        $res = $size->update($sizeData);
        $sizeCollection = new SizeResource($size);
        if ($res) {
            return response()->json($sizeCollection, 200);
        } else {
            return response()->json(['error', 'Sửa size thất bại']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $size = Size::FindorFail($id);
        $size->delete();

        return response()->json(['message' => 'xóa thành công']);
    }
}
