<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'categories';

    protected $fillable = [
        'name',
        'description',
        'image',
        'status'
    ];

    public function subcategories()
    {
        return $this->hasMany(Subcategory::class, 'categorie_id', 'id');
    }
}
