<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        try {
            $users = User::paginate(10);

            return response()->json([
                'user' => new UserCollection($users),
            ], 200);
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
                'user' => new UserResource($user),
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
                'user' => new UserResource($user),
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
}
