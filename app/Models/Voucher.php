<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function bills()
    {
        return $this->belongsToMany(Bill::class, 'bill_vouchers', 'voucher_id', 'bill_id')
            ->withTimestamps();
    }

    public function scopeFilter($query, $filters)
    {
        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('start_date', [$filters['start_date'], $filters['end_date']]);
        } elseif (!empty($filters['end_date'])) {
            $query->where('start_date', '<=', $filters['end_date']);
        } elseif (!empty($filters['start_date'])) {
            $query->where('start_date', '>=', $filters['start_date']);
        } else {
            $query->where('start_date', '>=', now()->format('Y-m-d'));
        }

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        return $query;
    }



    protected $fillable = [
        'name',
        'value',
        'discount_percentage',
        'max_discount_value',
        'image',
        'start_date',
        'end_date',
        'status',
        'customer_id',
        'quantity'
    ];
}
