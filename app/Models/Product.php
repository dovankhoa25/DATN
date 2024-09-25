<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'thumbnail', 'status', 'description', 'category_id'];


    public function scopeFilter($query, $filters)
    {
        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
        }

        if (!empty($filters['sort_by']) && !empty($filters['orderby'])) {
            $query->orderBy($filters['sort_by'], $filters['orderby']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }







    // Mối quan hệ với ProductDetail

    public function productDetails()
    {
        return $this->hasMany(ProductDetail::class, 'product_id');
    }

    // Mối quan hệ với Image thông qua ProductDetail
    public function images()
    {
        return $this->hasManyThrough(Image::class, ProductDetail::class);
    }
    public function sizes()
    {
        return $this->hasManyThrough(Size::class, ProductDetail::class, 'product_id', 'id', 'id', 'size_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
