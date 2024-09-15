<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Table extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tables';

    public function scopeFilter($query, $filters)
    {
        if (!empty($filters['table'])) {
            $query->where('table', 'like', '%' . $filters['table'] . '%');
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
        'table',
        'description',
        'status'
    ];
}
