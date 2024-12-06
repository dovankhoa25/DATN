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
        'customer_id',
        'user_addresses_id',
        'order_date',
        'total_amount',
        'branch_address',
        'payment_id',
        'voucher_id',
        'note',
        'order_type',
        'table_number',
        'status',
        'payment_status',
        'qr_expiration',
        'payment_status',
    ];
    protected $dates = ['order_date'];

    public function billDetails()
    {
        return $this->hasMany(BillDetail::class, 'bill_id', 'id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function vouchers()
    {
        return $this->belongsToMany(Voucher::class, 'bill_vouchers', 'bill_id', 'voucher_id')
            ->withTimestamps();
    }

    public function UserAddress()
    {
        return $this->belongsTo(UserAddress::class, 'user_addresses_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tables()
    {
        return $this->belongsToMany(Table::class, 'bill_table');
    }

    public function shipper()
    {
        return $this->belongsTo(User::class, 'shiper_id', 'id');
    }

    public function scopeFilter($query, $filters)
    {
        if (isset($filters['ma_bill'])) {
            $query->where('ma_bill', 'like', '%' . $filters['ma_bill'] . '%');
        }

        if (isset($filters['order_date'])) {
            $query->whereDate('order_date', $filters['order_date']);
        }

        if (isset($filters['order_type'])) {
            $query->where('order_type', $filters['order_type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['table_number'])) {
            $query->where('table_number', $filters['table_number']);
        }

        if (isset($filters['branch_address'])) {
            $query->where('branch_address', 'like', '%' . $filters['branch_address'] . '%');
        }

        return $query;
    }
}
