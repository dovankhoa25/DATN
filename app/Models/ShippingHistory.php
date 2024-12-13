<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'bill_id',
        'admin_id',
        'shipper_id',
        'event',
        'description',
        'image_url'
    ];
    public function shipper()
    {
        return $this->belongsTo(User::class, 'shipper_id');
    }
    public function bill()
    {
        return $this->belongsTo(Bill::class, 'bill_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
