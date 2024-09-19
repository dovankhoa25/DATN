<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\FilterClientRequest;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClientKeyController extends Controller
{

    public function index(FilterClientRequest $request){
        try {
            
            $perPage = $request->get('per_page', 10);
            $apiKey = Client::filter($request)->paginate($perPage);
            return ClientResource::collection($apiKey);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Không tìm thấy Category'], 404);
        }
    }


    public function store(StoreClientRequest $request) {
        $apiKey = Str::random(60);
        
        Client::create([
            'name' => $request->name,
            'api_key' => hash('sha256', $apiKey),
        ]);
    
        return response()->json([
            'message' => 'store api key ok',
            'api_key' => $apiKey
        ], 201);
    }

    public function statusKey(Request $request, string $id)
    {
        try {
            $client = Client::findOrFail($id);
            $client->status = !$client->status;
            $client->save();

            if ($client->status) {
                return response()->json(['message' => 'mở khóa'], 200);
            } else {
                return response()->json(['message' => 'khóa'], 200);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => ' không tồn tại'], 404);
        }
    }
    
}
