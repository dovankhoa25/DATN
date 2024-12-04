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

    public function index(FillterTableRequest $request)
    {
        try {
            $perPage = $request->get('per_page', 10);

            $tables = Table::filter($request)
                ->latest()
                ->paginate($perPage);
            return TableResource::collection($tables);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Không tìm thấy Table'], 404);
        }
    }


    public function store(TableRequest $request)
    {
        try {
            $table = Table::create([
                'table' => $request->get('table'),
                'description' => $request->get('description') ?? Null,
                'status' => 1,
                'reservation_status' => 'close',
                'min_guest' => $request->get('min_guest') ?? null,
                'max_guest' => $request->get('max_guest') ?? null,
                'deposit' => $request->get('deposit') ?? null,
            ]);
            return response()->json([
                'data' => new TableResource($table),
                'message' => 'success'
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'thêm table thất bại'], 404);
        }
    }

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

    public function update(TableRequest $request, string $id)
    {
        try {
            $table = Table::findOrFail($id);

            $table->update([
                'table' => $request->get('table', $table->table),
                'description' => $request->get('description', $table->description),
                'status' => $request->get('status', $table->status),
                'reservation_status' => $request->get('reservation_status', $table->reservation_status),
                'min_guest' => $request->get('min_guest', $table->min_guest),
                'max_guest' => $request->get('max_guest', $table->max_guest),
                'deposit' => $request->get('deposit', $table->deposit),
            ]);

            return response()->json([
                'data' => new TableResource($table),
                'message' => 'Cập nhật bàn thành công',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Bàn không tồn tại'], 404);
        }
    }


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

    public function updateStatus(Request $request, string $id)
    {
        try {
            $table = Table::findOrFail($id);
            $table->status = !$table->status;
            $table->save();

            if ($table->status) {
                return response()->json(['message' => 'hiện'], 200);
            } else {
                return response()->json(['message' => 'ẩn'], 200);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'table không tồn tại'], 404);
        }
    }
}
