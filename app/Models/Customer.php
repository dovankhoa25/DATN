<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function scopeFilter($query, $filters)
    {
        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['email'])) {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }

        if (!empty($filters['phone'])) {
            $query->where('phone', 'like', '%' . $filters['phone'] . '%');
        }

        if (!empty($filters['sort_by']) && !empty($filters['orderby'])) {
            $query->orderBy($filters['sort_by'], $filters['orderby']);
        }

        return $query;
    }



    protected $fillable = [
        "name",
        "email",
        "phone_number",
        "diemthuong",
        "user_id",
    ];

    protected $table = 'customers';
}
