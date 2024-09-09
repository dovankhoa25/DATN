<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;
    protected $fillable = ['name','product_detail_id'];

    // Mối quan hệ với ProductDetail
    public function productDetail()
    {
        return $this->belongsTo(ProductDetail::class);
    }
    
}
