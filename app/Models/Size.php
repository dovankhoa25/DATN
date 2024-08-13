<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'created_at',
        'updated_at'
    ];

    public $table = 'sizes';
    public $timestamp = false;
    
}
