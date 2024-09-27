<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDetail extends Model
{
    use HasFactory;

    protected $fillable = ['size_id', 'price', 'quantity', 'product_id', 'sale', 'status'];

    // quan hệ Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }


    //  quan hệ Image
    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function size()
    {
        return $this->belongsTo(Size::class, 'size_id');
    }
}
