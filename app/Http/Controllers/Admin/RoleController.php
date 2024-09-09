<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'per_page' => 'integer|min:1|max:100'
        ]);
        $perPage = $validated['per_page'] ?? 10;
        $roles =  Role::paginate($perPage); 

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
            'data' => new RoleResource($role),
        ], 201);
    }

    public function show($id)
    {
        $role = Role::findOrFail($id);
        return response()->json([
            'data' => new RoleResource($role),
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            // 'status' => 'sometimes|boolean', 
        ]);
        // $validatedData['status'] = true;
        

        $role->update($validatedData);
    
        return response()->json([
            'data' => new RoleResource($role),
        ], 201);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return response()->json(null, 204); 
        
    }

    
}
