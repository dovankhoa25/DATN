<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
