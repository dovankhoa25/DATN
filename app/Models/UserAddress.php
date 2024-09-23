<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    use HasFactory;
    protected $table = 'user_addresses';

    protected $fillable = [
        'user_id',
        'address',
        'city',
        'state',
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

    

}
