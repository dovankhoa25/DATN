<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillTable extends Model
{
    protected $fillable = ['bill_id', 'table_id'];

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }
}
