<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class TransactionHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'gateway',
        'transaction_date',
        'account_number',
        'code',
        'content',
        'transfer_type',
        'transfer_amount',
        'accumulated',
        'sub_account',
        'reference_code',
        'description',
    ];

    public function scopeFilter($query, $request)
    {
        if ($request->has('gateway') && $request->gateway) {
            $query->where('gateway', $request->gateway);
        }

        if ($request->has('transaction_date') && $request->transaction_date) {
            $query->whereDate('transaction_date', $request->transaction_date);
        }

        if ($request->has('account_number') && $request->account_number) {
            $query->where('account_number', 'like', '%' . $request->account_number . '%');
        }

        if ($request->has('code') && $request->code) {
            $query->where('code', 'like', '%' . $request->code . '%');
        }
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('transaction_date', [$request->start_date, $request->end_date]);
        }

        $query->orderBy('transaction_date', 'desc');
    }
}
