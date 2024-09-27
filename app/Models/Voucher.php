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
        return $this->hasMany(Bill::class, 'voucher_id');
    }

    public function scopeFilter($query, $filters)
    {
        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['value'])) {
            $query->where('value', $filters['value']);
        }

        if (!empty($filters['expiration_date'])) {
            $query->whereDate('expiration_date', $filters['expiration_date']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query;
    }

    protected $fillable = [
        'name',
        'value',
        'image',
        'status',
        'customer_id',
        'expiration_date',
    ];
}
