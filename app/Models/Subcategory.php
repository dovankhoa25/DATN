<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subcategory extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'sub_categories';

    protected $fillable = [
        'name',
        'description',
        'image',
        'status',
        'categorie_id'
    ];
}
