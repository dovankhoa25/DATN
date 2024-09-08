<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'ma_bill',
        'product_detail_id',
        'quantity',
    ];

    protected $table = 'carts';

    public $timestamp = false;

    public function listCart()
    {
        $query = DB::table('carts as cart')
            ->select(
                'cart.id',
                'cart.ma_bill',
                'cart.quantity',

                'pro.name as product_name',
                'pro.thumbnail as product_thumbnail',
                'pro_detail.price as product_price',
                'pro_detail.sale as product_sale',
                'pro_detail.quantity as product_quantity',

                'size.name as size_name'
            )
            ->join('product_details as pro_detail', 'cart.product_detail_id', '=', 'pro_detail.id')
            ->join('products as pro', 'pro_detail.product_id', '=', 'pro.id')
            ->join('sizes as size', 'pro_detail.size_id', '=', 'size.id')
            ->orderBy('cart.id')
            // ->where('cart.id', 1)
            ->paginate(7);
    
        return $query;
    }

    public function cartByBillCode(string $ma_bill)
    {
        $query = DB::table('carts as cart')
            ->select(
                'cart.id',
                'cart.ma_bill',
                'cart.quantity',

                'pro.name as product_name',
                'pro.thumbnail as product_thumbnail',
                'pro_detail.price as product_price',
                'pro_detail.sale as product_sale',

                'size.name as size_name'
            )
            ->join('product_details as pro_detail', 'cart.product_detail_id', '=', 'pro_detail.id')
            ->join('products as pro', 'pro_detail.product_id', '=', 'pro.id')
            ->join('sizes as size', 'pro_detail.size_id', '=', 'size.id')
            ->orderBy('cart.id')
            ->where('cart.ma_bill', $ma_bill)
            ->paginate(5);
    
        return $query;
    }
    
}
