<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TimeOrderTable\FillterTimeOrderTableRequest;
use App\Http\Requests\TimeOrderTable\TimeOrderTableRequest;
use App\Http\Resources\TimeOrderTableResource;
use App\Models\Table;
use App\Models\TimeOrderTable;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class TimeOrderTableController extends Controller
{

    public function index(FillterTimeOrderTableRequest $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $timeOrderTable = TimeOrderTable::filter($request)->paginate($perPage);
            return TimeOrderTableResource::collection($timeOrderTable);
        } catch (Exception  $e) {
            return response()->json(['error' => 'Có lỗi xảy ra, vui lòng thử lại sau'], 404);
        }
    }


    public function store(TimeOrderTableRequest $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $idTable = $request->get('table_id');
            $findTable = Table::find($idTable);

            if ($findTable->status) {
                $timeSlots = [
                    'sáng' => '07:00:00',
                    'trưa' => '12:00:00',
                    'tối'  => '19:00:00',
                ];
                $timeOrderTable = TimeOrderTable::create([
                    'table_id' => $request->get('table_id'),
                    'user_id' => $user->id,
                    'phone_number' => $request->get('phone_number'),
                    'date_oder' => $request->get('date_oder'),
                    'time_oder' => $timeSlots[$request->get('time_oder')],
                    'status' => 'pending',
                    'description' => $request->get('description'),
                ]);
                return response()->json([
                    'data' => new TimeOrderTableResource($timeOrderTable),
                    'message' => 'success'
                ], 201);
            } else {
                return response()->json([
                    'message' => 'Thất bại vì bàn này không đặt được'
                ], 400);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'thêm timeOrderTable thất bại'], 404);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Xác thực JWT thất bại'], 401);
        } catch (Exception $e) {
            return response()->json(['error' => 'Đã xảy ra lỗi, vui lòng thử lại sau'], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $timeOrderTable = TimeOrderTable::findOrFail($id);
            return response()->json([
                'data' => new TimeOrderTableResource($timeOrderTable),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'timeOrderTable không tồn tại'], 404);
        }
    }

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
            return response()->json(['error' => 'Không tìm thấy TimeOrderTable để update'], 404);
        }
    }

    public function destroy(string $id)
    {
        try {
            $timeOrderTable = TimeOrderTable::findOrFail($id);

            $timeOrderTable->delete();
            return response()->json([
                'message' => 'xoá timeOrderTable thành công'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'timeOrderTable không tồn tại'], 404);
        }
    }
}
