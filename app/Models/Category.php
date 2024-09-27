<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'categories';

    public function scopeFilter($query, $filters)
    {
        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['sort_by']) && !empty($filters['orderby'])) {
            $query->orderBy($filters['sort_by'], $filters['orderby']);
        }

        return $query;
    }

    protected $fillable = [
        'name',
        'description',
        'image',
        'status',
        'parent_id'
    ];


    public function subcategories()
    {
        return $this->hasMany(Category::class, 'parent_id')->with('subcategories');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
}
