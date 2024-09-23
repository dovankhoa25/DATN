<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Table\FillterTableRequest;
use App\Http\Requests\Table\TableRequest;
use App\Http\Resources\TableResource;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class TablesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(FillterTableRequest $request)
    {
        try {
            $perPage = $request->get('per_page', 10);

            $tables = Table::filter($request)->paginate($perPage);
            return TableResource::collection($tables);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Không tìm thấy Category'], 404);
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
    public function store(TableRequest $request)
    {
        try {
            $table = Table::create([
                'table' => $request->get('table'),
                'description' => $request->get('description') ?? Null,
            ]);
            return response()->json([
                'data' => new TableResource($table),
                'message' => 'success'
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'thêm table thất bại'], 404);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        try {
            $table = Table::findOrFail($id);
            return response()->json([
                'table' => new TableResource($table),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'table không tồn tại'], 404);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(TableRequest $request, string $id)
    {
        try {
            $table = Table::findOrFail($id);

            $table->update([
                'table' => $request->get('table'),
                'description' => $request->get('description') ?? Null,
            ]);
            return response()->json([
                'data' => new TableResource($table),
                'message' => 'success',
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'table không tồn tại'], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $table = Table::findOrFail($id);

            $table->delete(); // Xóa mềm
            return response()->json([
                'message' => 'xoá table thành công'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'table không tồn tại'], 404);
        }
    }
}
