<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles =  Role::paginate(10); 
        return RoleResource::collection($roles);
        
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $validatedData['status'] = true;


        $role = Role::create($validatedData);

        return response()->json([
            'role' => new RoleResource($role),
        ], 201);
    }

    public function show($id)
    {
        $role = Role::findOrFail($id);
        return response()->json([
            'role' => new RoleResource($role),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'status' => 'sometimes|boolean',
        ]);
    

        $role->update($validatedData);
    
        return response()->json([
            'role' => new RoleResource($role),
        ], 201);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return response()->json(null, 204); 
        
    }
}
