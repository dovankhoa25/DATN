<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        try {
            $validated = $request->validate([
                'per_page' => 'integer|min:1|max:100'
            ]);
            $perPage = $validated['per_page'] ?? 10;
            $users = User::paginate($perPage);

            return new UserCollection($users);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User rỗng'], 404);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {

        try {
            $user = User::findOrFail($id);
            return response()->json([
                'data' => new UserResource($user),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User không tồn tại'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
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


}
