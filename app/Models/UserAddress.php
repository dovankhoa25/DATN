<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAddress extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_addresses';

    protected $fillable = [
        'user_id',
        'fullname',
        'phone',
        'province',
        'district',
        'commune',
        'address',
        'postal_code',
        'country',
        'is_default',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bills()
    {
        return $this->hasMany(Bill::class, 'user_addresses_id');
    }

    public function getFullAddressAttribute()
    {
        return "Tên : {$this->fullname}, Phone : {$this->phone}, Địa chỉ : {$this->address},
         {$this->commune}, {$this->district}, {$this->province}, {$this->country}";
    }
}
