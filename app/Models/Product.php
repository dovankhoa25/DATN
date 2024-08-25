<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'thumbnail', 'status', 'sub_categories_id'];

    // Mối quan hệ với ProductDetail
    public function productDetails()
    {
        return $this->hasMany(ProductDetail::class);
    }

    // Mối quan hệ với Image thông qua ProductDetail
    public function images()
    {
        return $this->hasManyThrough(Image::class, ProductDetail::class);
    }
}
