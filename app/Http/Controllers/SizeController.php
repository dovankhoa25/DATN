<?php

namespace App\Http\Controllers;

use App\Models\Size;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $listSize = Size::all();
        return response()->json($listSize);
        // dd($listSize);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request -> all();

        $size = Size::create([
            'name' => $request['name'],
            'status' => $request['status']
        ]);

        return response()->json($size, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $size = Size::FindorFail($id);
        $request -> all();
        
        $size -> update([
            'name' => $request['name'],
            'status' => $request['status']
        ]);

        return response()->json($size, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $size = Size::FindorFail($id);
        $size->delete();

        return response()->json(['message' => 'xóa thành công']);
    }
}
