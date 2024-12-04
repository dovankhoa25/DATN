<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillVoucher extends Model
{
    use HasFactory;

    protected $table = 'bill_vouchers';

    protected $fillable = [
        'bill_id',
        'voucher_id',
    ];

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}
