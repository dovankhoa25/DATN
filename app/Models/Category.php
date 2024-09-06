<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $table = 'categories';

    protected $fillable = [
        'name',
        'description',
        'image',
        'status',
        'parent_id'
    ];

    public function subcategories()
    {
        return $this->hasMany(Category::class, 'parent_id')->where('status', true);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id')->where('status', true);
    }
}
