<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'status',
        'created_at',
        'updated_at'
    ];

    public $table = 'payments';
    
    public $timestamp = false;


    public function scopeFilter($query, $filters){
        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }
        if (!empty($filters['sort_by']) && !empty($filters['orderby'])) {
            $query->orderBy($filters['sort_by'], $filters['orderby']);
        }
        return $query;
    }

    public function bills()
    {
        return $this->hasMany(Bill::class, 'payment_id');

    }
}
