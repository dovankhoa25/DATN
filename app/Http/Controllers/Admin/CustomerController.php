<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CustomerRequest;
use App\Http\Requests\Customer\FilterCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    
    public function index(FilterCustomerRequest $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            // $customers = Customer::filter($request->all())->paginate($perPage);
            $customers = Customer::filter($request)->paginate($perPage);
            return CustomerResource::collection($customers);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Không tìm thấy khách hàng'], 404);
        }
    }
    

   
    public function store(CustomerRequest $request)
    {

        $customer = Customer::create(   [
            "name" => $request->get('name'),
            "email" => $request->get('email'),
            "phone_number" => $request->get('phone_number'),
            "diemthuong" => $request->get('diemthuong') ?: 0,
            "user_id" => $request->get('user_id') ?: null
        ]);

        return response()->json([
            'data' => new CustomerResource($customer)
        ], 201);
    }

    
    public function show(Customer $customer)
    {
        return response()->json([
            'data' => new CustomerResource($customer)
        ], 200);
    }

    
    public function update(CustomerRequest $request, Customer $customer)
    {
        $newPoints = $request->get('diemthuong', 0);

        $customer->update([
            "diemthuong" => $customer->diemthuong + $newPoints,
            "user_id" => $request->get('user_id', null)
        ]);

        return response()->json([
            'data' => new CustomerResource($customer)
        ], 200);
    }


    public function destroy(Customer $customer)
    {
        $customer->delete();

        return response()->json([
            'message' => 'Customer deleted successfully',
        ], 200);
    }
}
