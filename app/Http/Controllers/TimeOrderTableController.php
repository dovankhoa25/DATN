<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\TimeOrderTableRequest;
use App\Http\Resources\TimeOrderTableResource;
use App\Models\TimeOrderTable;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TimeOrderTableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = TimeOrderTable::paginate(10);
        $timeOrderTable = TimeOrderTableResource::collection($data);
        return $timeOrderTable;
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
    public function store(TimeOrderTableRequest $request)
    {
        try {
            $timeOrderTable = TimeOrderTable::create([
                'table_id' => $request->get('table_id'),
                'user_id' => $request->get('user_id'),
                'phone_number' => $request->get('phone_number'),
                'date_oder' => $request->get('date_oder'),
                'time_oder' => $request->get('time_oder'),
                'description' => $request->get('description'),
            ]);
            return response()->json([
                'table' => new TimeOrderTableResource($timeOrderTable),
                'message' => 'success'
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'thêm table thất bại'], 404);
        }

        // return response()->json(['error' => $request->all()], 404);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $timeOrderTable = TimeOrderTable::findOrFail($id);
            return response()->json([
                'timeOrderTable' => new TimeOrderTableResource($timeOrderTable),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'timeOrderTable không tồn tại'], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,completed,failed'
            ]);

            $timeOrderTable = TimeOrderTable::findOrFail($id);

            $timeOrderTable->status = $request->input('status');
            $timeOrderTable->save();

            return response()->json([
                'message' => 'Status updated successfully',
                'data' => new TimeOrderTableResource($timeOrderTable)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'update status thất bại'], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
