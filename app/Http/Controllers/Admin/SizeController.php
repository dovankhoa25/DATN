<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Size\FilterSizeRequest;
use App\Http\Requests\SizeRequest;
use App\Http\Resources\SizeResource;
use App\Models\Size;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(FilterSizeRequest $request)
    {
        $perPage = $request['per_page'] ?? 10;
        $listSize = Size::filter($request)->paginate($perPage);
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


    public function show(string $id)
    {
        $size = Size::FindorFail($id);
        if ($size) {
            return response()->json($size, 200);
        } else {
            return response()->json(['error', 'Không tìm thấy size theo id']);
        }
    }

 
    public function update(SizeRequest $request, string $id)
    {
        $size = Size::FindorFail($id);
        $sizeData = $request->all();

        $res = $size->update($sizeData);
        if ($res) {
            return response()->json($size, 200);
        } else {
            return response()->json(['error', 'Sửa size thất bại']);
        }
    }

    
    public function destroy(string $id)
    {
        $size = Size::FindorFail($id);
        $size->delete();

        return response()->json(['message' => 'xóa thành công']);
    }




    public function statusSize(Request $request, string $id)
    {
        try {
            $size = Size::findOrFail($id);
            $size->status = !$size->status;
            $size->save();

            if ($size->status) {
                return response()->json(['message' => 'hiện'], 200);
            } else {
                return response()->json(['message' => 'ẩn'], 200);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => ' không tồn tại'], 404);
        }
    }

    
}
