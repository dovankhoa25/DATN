<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TableRequest;
use App\Http\Resources\TableResource;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class TablesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'per_page' => 'integer|min:1|max:100',
            'table' => 'string|nullable',
            'status' => 'boolean|nullable',
        ]);
        $perPage = $validated['per_page'] ?? 10;

        $query = Table::query();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('table')) {
            $query->where('table', 'like', '%' . $request->input('table') . '%');
        }

        $tables = $query->paginate($perPage);
        return TableResource::collection($tables);
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
                'description' => $request->get('description'),
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
                'description' => $request->get('description'),
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
