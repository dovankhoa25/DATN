<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;
    protected $table = 'bills';
    protected $fillable = [
        'ma_bill',
        'user_id',
        'order_date',
        'total_money',
        'address',
        'payment_id',
        'voucher_id',
        'note',
        'status'
    ];

    public function billDetails()
    {
        return $this->hasMany(BillDetail::class, 'bill_id', 'id');
    }
}
