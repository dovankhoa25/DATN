<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\TransactionRequest;
use App\Models\Bill;
use App\Models\TransactionHistory;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function webhook(TransactionRequest $request)
    {

        $apiKey = $request->header('Authorization');

        if ($apiKey !== 'Apikey ' . config('sepay.api_key')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }


        TransactionHistory::create([
            'gateway' => $request['gateway'],
            'transaction_date' => $request['transactionDate'],
            'account_number' => $request['accountNumber'],
            'code' => $request['code'] ?? null,
            'content' => $request['content'] ?? null,
            'transfer_type' => $request['transferType'],
            'transfer_amount' => $request['transferAmount'],
            'accumulated' => $request['accumulated'],
            'sub_account' => $request['subAccount'] ?? null,
            'reference_code' => $request['referenceCode'] ?? null,
            'description' => $request['description'] ?? null,
        ]);

        $donhang = $request['content'];
        preg_match('/\d+/', $donhang, $matches);
        $billId = $matches[0] ?? null;

        if ($billId) {
            $bill = Bill::find($billId);

            if ($bill) {
                if ($bill->total_amount <= $request['transferAmount']) {
                    $bill->payment_status = 'successful';
                    $bill->save();
                }
            }
        }

        return response()->json(['success' => true], 201);
    }
}
