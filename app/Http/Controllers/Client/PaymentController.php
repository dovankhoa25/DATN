<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function listPaymentTrue(Request $request)
    {
        $perPage = $request['per_page'] ?? 10;

        $payments = Payment::where('status', true)->select('id', 'name')->get();

        return response()->json([
            'data' => $payments,
        ], 201);
    }
}
