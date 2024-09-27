<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\FilterUserRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function index(FilterUserRequest $request)
    {
        try {
            // $filters = $request->all();
            // dd($filters);
            
            $perPage = $request['per_page'] ?? 10;
            $users = User::with('roles')->filter($request)->paginate($perPage);
            return new UserCollection($users);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Không tìm thấy người dùng'], 404);
        }
    }



    public function store(Request $request) {}


    public function show($id)
    {

        try {
            $user = User::with('roles')->findOrFail($id);
            return response()->json([
                'data' => new UserResource($user),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User không tồn tại'], 404);
        }
    }


    public function update(Request $request, string $id)
    {

        try {

            $user = User::findOrFail($id);

            $validatedData = $request->validate([
                'password' => 'required|string|min:6|max:255',
            ]);

            $user->update([
                'password' => Hash::make($validatedData['password']),
            ]);
            return response()->json([
                'data' => new UserResource($user),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User không tồn tại'], 404);
        }
    }

    public function destroy(string $id)
    {
        try {

            $user = User::findOrFail($id);
            $user->delete(); // Xóa mềm
            return response(200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User không tồn tại'], 404);
        }
    }

    public function getUserRoles(User $user)
    {
        $roles = Role::all();
        $userRoles = $user->roles->pluck('id');
        return response()->json([
            'data' => [
                'roles' => $roles,
                'userRoles' => $userRoles,
            ]
        ]);
    }


    public function updateUserRoles(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'integer|exists:roles,id',
        ]);

        $user->roles()->sync($validatedData['roles']);
        return response()->json(['message' => 'Update thành công']);
    }


    public function is_locked(Request $request, string $id)
    {
        try {
            $user = User::findOrFail($id);
            $user->is_locked = !$user->is_locked;
            $user->save();

            if ($user->is_locked) {
                return response()->json(['message' => 'User đã bị khóa'], 200);
            } else {
                return response()->json(['message' => 'User đã được mở khóa'], 200);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User không tồn tại'], 404);
        }
    }
}
