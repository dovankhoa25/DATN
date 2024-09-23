<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillDetail extends Model
{
    use HasFactory;
    // Định nghĩa quan hệ với Bill
    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    // Định nghĩa quan hệ với ProductDetail
    public function productDetail()
    {
        return $this->belongsTo(ProductDetail::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
