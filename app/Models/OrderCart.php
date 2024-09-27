<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderCart extends Model
{
    use HasFactory;

    protected $fillable = [
        'ma_bill',
        'product_detail_id',
        'quantity',
        'price',
    ];
    protected $table = 'oder_cart';

    public $timestamp = false;

    public function listCart()
    {
        $query = DB::table('oder_cart as od_cart')
            ->select(
                'od_cart.id',
                'od_cart.ma_bill',
                'od_cart.quantity',
                'od_cart.price',
                'od_cart.product_detail_id',

                'pro.name as product_name',
                'pro.thumbnail as product_thumbnail',

                'size.name as size_name',
            )
            ->join('product_details as pro_detail', 'od_cart.product_detail_id', '=', 'pro_detail.id')
            ->join('products as pro', 'pro_detail.product_id', '=', 'pro.id')
            ->join('sizes as size', 'pro_detail.size_id', '=', 'size.id');

        return $query;
    }
}
