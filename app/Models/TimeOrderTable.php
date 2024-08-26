<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeOrderTable extends Model
{
    use HasFactory;
    protected $table = 'time_order_table';

    protected $fillable = [
        'table_id',
        'user_id',
        'phone_number',
        'date_oder',
        'time_oder',
        'description',
        'status'
    ];
}
