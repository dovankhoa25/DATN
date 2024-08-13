<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = Customer::all();
        $total = $customers->count();

        return response()->json([
            'data' => CustomerResource::collection($customers),
            'total' => $total,
            'status' => 'success',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerRequest $request)
    {

        $customer = Customer::create([
            "name" => $request->get('name'),
            "phone_number" => $request->get('phone_number'),
            "user_id" => $request->get('user_id') || null
        ]);

        return response()->json([
            'data' => new CustomerResource($customer),
            'status' => 'success',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return response()->json([
            'data' => new CustomerResource($customer),
            'status' => 'success',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerRequest $request, Customer $customer)
    {

        $customer->update([
            "name" => $request->get('name'),
            "phone_number" => $request->get('phone_number'),
            "user_id" => $request->get('user_id') || null
        ]);

        return response()->json([
            'data' => new CustomerResource($customer),
            'status' => 'success',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Customer deleted successfully',
        ]);
    }
}
