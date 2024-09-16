<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TimeOrderTable extends Model
{
    use HasFactory;
    protected $table = 'time_order_table';

    public function scopeFilter($query, $filters)
    {
        if (!empty($filters['phone_number'])) {
            $query->where('phone_number', 'like', '%' . $filters['phone_number'] . '%');
        }

        if (!empty($filters['date_oder'])) {
            $query->where('date_oder', $filters['date_oder']);
        }

        if (!empty($filters['time_oder'])) {
            $query->where('time_oder', $filters['time_oder']);
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
        'table_id',
        'user_id',
        'phone_number',
        'date_oder',
        'time_oder',
        'description',
        'status'
    ];

    public function timeOrderByTableId(int $idTable)
    {
        $query = DB::table('time_order_table as t_o_table')
            ->select(
                't_o_table.id',
                't_o_table.phone_number',
                't_o_table.date_oder',
                't_o_table.time_oder',
                't_o_table.description',
                't_o_table.status',

                'table.table as table_name',

                'user.name as user_name'
            )
            ->join('tables as table', 't_o_table.table_id', '=', 'table.id')
            ->join('users as user', 't_o_table.user_id', '=', 'user.id')
            ->orderBy('t_o_table.id')
            ->where('t_o_table.table_id', $idTable);

        return $query;
    }
}
